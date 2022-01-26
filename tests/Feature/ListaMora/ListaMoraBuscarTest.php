<?php

namespace Tests\Feature\ListaMora;

use App\Models\ListaMoraItem;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ListaMoraBuscarTest extends TestCase
{
    private function assertSuccess(TestResponse $response, $meta, $data)
    {
        $response->assertOk();
        $response->assertJson([
            "meta" => $meta,
            "records" => $data->toArray()
        ]);
    }

    public function test_usuario_puede_buscar()
    {
        $login = User::factory()
            ->withPermissions([
                Permisos::VER_LISTA_DE_MORA
            ])
            ->create();

        ListaMoraItem::factory()->count(20)->create();
        
        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => [ "current" => 1, "size" => 10]
        ]));
        $this->assertSuccess($response, [
            "total" => 20,
            "nextPage" => 2
        ], ListaMoraItem::limit(10)->get());
    }

    public function test_usuario_puede_buscar_solo_dentro_de_su_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::VER_LISTA_DE_MORA_REGIONAL
            ])
            ->create();
            
        $itemLaPaz = ListaMoraItem::factory()->regionalLaPaz()->create();
        ListaMoraItem::factory()->regionalSantaCruz()->create();
        
        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => [ "current" => 1, "size" => 10]
        ]));
        $response->assertForbidden();
        
        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => [ "current" => 1, "size" => 10],
            "filter" => [ "regional_id" => 3]
        ]));
        $response->assertForbidden();
        
        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => [ "current" => 1, "size" => 10],
            "filter" => [ "regional_id" => 1]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$itemLaPaz]));

        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::VER_LISTA_DE_MORA,
                Permisos::VER_LISTA_DE_MORA_REGIONAL
            ])
            ->create();
            
        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => [ "current" => 1, "size" => 10]
        ]));
        $response->assertForbidden();
        
        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => [ "current" => 1, "size" => 10],
            "filter" => [ "regional_id" => 3]
        ]));
        $response->assertForbidden();
    }

    public function test_filter_by_nombre()
    {
        $login = $this->getSuperUser();

        $laboratorio = ListaMoraItem::factory()->state([
            "nombre" => "Laboratorio patito"
        ])->create();
        $other = ListaMoraItem::factory()->state([
            "nombre" => "Clinica nuclear",
        ])->create();        

        DB::commit();

        $page = [
            "current" => 1,
            "size" => 10
        ];
        
        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page
        ]));
        $this->assertSuccess($response, [
            "total" => 2
        ], collect([$laboratorio, $other]));

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page,
            "filter" => [
                "nombre" => "labo"
            ]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$laboratorio]));

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page,
            "filter" => [
                "nombre" => "nuc"
            ]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$other]));

        RefreshDatabaseState::$migrated = false;
        $this->refreshDatabase();
    }

    public function test_filter_by_numero_patronal()
    {
        $login = $this->getSuperUser();

        $item = ListaMoraItem::factory([
            "numero_patronal" => "999-01001"
        ])->create();
        $other = ListaMoraItem::factory([
            "numero_patronal" => "999-01002"
        ])->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page
        ]));
        $this->assertSuccess($response, [
            "total" => 2
        ], collect([$item, $other]));

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page,
            "filter" => ["numero_patronal" => "999-01001"]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$item]));

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page,
            "filter" => ["numero_patronal" => "999-01002"]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$other]));
    }

    public function test_busqueda()
    {
        $login = $this->getSuperUser();

        $centro = ListaMoraItem::factory([
            "numero_patronal" => "999-01001",
            "nombre" => "Centro ofstalmologico"
        ])->create();
        $cenetrop = ListaMoraItem::factory([
            "numero_patronal" => "999-01002",
            "nombre" => "Laboratorio de analisis clinicos - Cenetrop"
        ])->create();
        $number = ListaMoraItem::factory([
            "numero_patronal" => "121-01002",
            "nombre" => "Hospital 999"
        ])->create();
        $patito = ListaMoraItem::factory([
            "numero_patronal" => "111-01002",
            "nombre" => "Laboratorio patito"
        ])->create();

        DB::commit();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page
        ]));
        $this->assertSuccess($response, [
            "total" => 4
        ], collect([$centro, $cenetrop, $number, $patito]));

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page,
            "filter" => ["_busqueda" => "999"]
        ]));
        $this->assertSuccess($response, [
            "total" => 3
        ], collect([$centro, $cenetrop, $number]));

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page,
            "filter" => ["_busqueda" => "ce"]
        ]));
        $this->assertSuccess($response, [
            "total" => 2
        ], collect([$centro, $cenetrop]));

        RefreshDatabaseState::$migrated = false;
        $this->refreshDatabase();
    }

    public function test_filter_by_regional()
    {
        $login = $this->getSuperUser();

        $itemLaPaz = ListaMoraItem::factory()->regionalLaPaz()->create();
        $proveedorSantaCruz = ListaMoraItem::factory()->regionalSantaCruz()->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page,
            "filter" => [
                "regional_id" => $itemLaPaz->regional_id
            ]
        ]));
        $this->assertSuccess($response,[
            "total" => 1
        ], collect([$itemLaPaz]));

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page,
            "filter" => [
                "regional_id" => $proveedorSantaCruz->regional_id
            ]
        ]));
        $this->assertSuccess($response,[
            "total" => 1
        ], collect([$proveedorSantaCruz]));

        $response = $this->actingAs($login)->getJson("/api/lista-mora?".http_build_query([
            "page" => $page
        ]));
        $this->assertSuccess($response,[
            "total" => 2
        ], collect([$itemLaPaz, $proveedorSantaCruz]));
    }
}
