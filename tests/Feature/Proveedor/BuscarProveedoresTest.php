<?php

namespace Tests\Feature\Proveedor;

use App\Models\ContratoProveedor;
use App\Models\Permisos;
use App\Models\Proveedor;
use App\Models\User;
// use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BuscarProveedoresTest extends TestCase
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
                Permisos::VER_PROVEEDORES
            ])
            ->create();

        for($i = 0; $i < 20; $i++) Proveedor::factory()->tipoRandom()->create();
        
        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => [ "current" => 1, "size" => 10]
        ]));
        $this->assertSuccess($response, [
            "total" => 20,
            "nextPage" => 2
        ], Proveedor::limit(10)->get());
    }

    public function test_usuario_puede_buscar_solo_dentro_de_su_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::VER_PROVEEDORES_REGIONAL
            ])
            ->create();
            
        $proveedorLaPaz = Proveedor::factory()->tipoRandom()->regionalLaPaz()->create();
        Proveedor::factory()->tipoRandom()->regionalSantaCruz()->create();
        
        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => [ "current" => 1, "size" => 10]
        ]));
        $response->assertForbidden();
        
        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => [ "current" => 1, "size" => 10],
            "filter" => [ "regional_id" => 3]
        ]));
        $response->assertForbidden();
        
        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => [ "current" => 1, "size" => 10],
            "filter" => [ "regional_id" => 1]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$proveedorLaPaz]));

        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::VER_PROVEEDORES,
                Permisos::VER_PROVEEDORES_REGIONAL
            ])
            ->create();
            
        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => [ "current" => 1, "size" => 10]
        ]));
        $response->assertForbidden();
        
        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => [ "current" => 1, "size" => 10],
            "filter" => [ "regional_id" => 3]
        ]));
        $response->assertForbidden();
    }

    public function test_filter_by_nombre()
    {
        $login = $this->getSuperUser();

        $lorena = Proveedor::factory()->medico()->state([
            "nombre" => "Lorena",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Fulanito"
        ])->create();
        $lorem =  Proveedor::factory()->medico()->state([
            "nombre" => "Cosme",
            "apellido_materno" => "Lörem",
            "apellido_paterno" => "Fulanito"
        ])->create();
        $lord = Proveedor::factory()->medico()->state([
            "nombre" => "Cosme",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Lord"
        ])->create();
        $loro = Proveedor::factory()->empresa()->state([
            "nombre" => "Papagayos y loros",
        ])->create();
        Proveedor::factory()->medico()->state([
            "nombre" => "Cosme",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Fulanito"
        ])->create();
        Proveedor::factory()->empresa()->state([
            "nombre" => "Consome",
        ])->create();

        DB::commit();

        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "nombre" => "lor"
        ];

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $this->assertSuccess($response, [
            "total" => 4
        ], collect([$lorena, $lorem, $lord, $loro]));
        
        RefreshDatabaseState::$migrated = false;
        $this->refreshDatabase();
    }

    public function test_filter_by_tipo()
    {
        $login = $this->getSuperUser();

        $medico = Proveedor::factory()->medico()->create();
        $empresa = Proveedor::factory()->empresa()->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page
        ]));
        $this->assertSuccess($response, [
            "total" => 2
        ], collect([$medico, $empresa]));

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => ["tipo" => 1]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$medico]));

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => ["tipo" => 2]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$empresa]));
    }

    public function test_filter_by_nit()
    {
        $login = $this->getSuperUser();

        $proveedor1 = Proveedor::factory([
            "nit" => "123456789012"
        ])->tipoRandom()->create();

        $proveedor2 = Proveedor::factory([
            "nit" => "234567890123"
        ])->tipoRandom()->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => ["nit" => $proveedor1->nit]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$proveedor1]));

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => ["nit" => $proveedor2->nit]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$proveedor2]));

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page
        ]));
        $this->assertSuccess($response, [
            "total" => 2
        ], collect([$proveedor1, $proveedor2]));
    }

    public function test_filter_by_estado()
    {
        $login = $this->getSuperUser();

        $activo = Proveedor::factory()->tipoRandom()->create();
        $baja = Proveedor::factory()->tipoRandom()->baja()->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => ["estado" => 1]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$activo]));

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => ["estado" => 2]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$baja]));

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page
        ]));
        $this->assertSuccess($response,[
            "total" => 2
        ],collect([$activo, $baja]));
    }

    public function test_filter_by_regional()
    {
        $login = $this->getSuperUser();

        $proveedorLaPaz = Proveedor::factory()->tipoRandom()->regionalLaPaz()->create();
        $proveedorSantaCruz = Proveedor::factory()->tipoRandom()->regionalSantaCruz()->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => [
                "regional_id" => $proveedorLaPaz->regional_id
            ]
        ]));
        $this->assertSuccess($response,[
            "total" => 1
        ], collect([$proveedorLaPaz]));

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page,
            "filter" => [
                "regional_id" => $proveedorSantaCruz->regional_id
            ]
        ]));
        $this->assertSuccess($response,[
            "total" => 1
        ], collect([$proveedorSantaCruz]));

        $response = $this->actingAs($login)->getJson("/api/proveedores?".http_build_query([
            "page" => $page
        ]));
        $this->assertSuccess($response,[
            "total" => 2
        ], collect([$proveedorLaPaz, $proveedorSantaCruz]));
    }
}
