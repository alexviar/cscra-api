<?php

namespace Tests\Feature\Medico;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BuscarMedicoTest extends TestCase
{
    private function assertSucces(TestResponse $response, $meta, $data)
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
                Permisos::VER_MEDICOS
            ])
            ->create();

        Medico::factory()->count(20)->create();
        
        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => [ "current" => 1, "size" => 10]
        ]));
        $this->assertSucces($response, [
            "total" => 20,
            "nextPage" => 2
        ], Medico::limit(10)->get());
    }

    public function test_usuario_puede_buscar_dentro_de_su_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::VER_MEDICOS_REGIONAL
            ])
            ->create();

        $medicoLaPaz = Medico::factory()->regionalLaPaz()->create();
        Medico::factory()->regionalSantaCruz()->create();

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => [ "current" => 1, "size" => 10]
        ]));
        $response->assertForbidden();
        
        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => [ "current" => 1, "size" => 10],
            "filter" => [ "regional_id" => 3 ]
        ]));
        $response->assertForbidden();

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => [ "current" => 1, "size" => 10],
            "filter" => [ "regional_id" => 1 ]
        ]));
        $this->assertSucces($response, [
            "total" => 1
        ], collect([$medicoLaPaz]));
    }

    public function test_text_search()
    {
        $login = $this->getSuperUser();

        $lorena = Medico::factory()->state([
            "nombre" => "Lorena",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Fulanito",
            "especialidad" => "Cirujano"
        ])->create();
        $lorem =  Medico::factory()->state([
            "nombre" => "Fulanito",
            "apellido_materno" => "Lörem",
            "apellido_paterno" => "Fulanito",
            "especialidad" => "Cirujano"
        ])->create();
        $lord = Medico::factory()->state([
            "nombre" => "Fulanito",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Lord",
            "especialidad" => "Cirujano"
        ])->create();
        $lorem2 = Medico::factory()->state([
            "nombre" => "Fulanito",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Fulanito",
            "especialidad" => "Lorem ipsum"
        ])->create();

        Medico::factory()->state([
            "nombre" => "Cosme",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Fulanito"
        ])->create();

        DB::commit();
        RefreshDatabaseState::$migrated = false;
        $this->beforeApplicationDestroyed(function(){
            $this->refreshDatabase();
            // Medico::truncate();
        });

        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "_busqueda" => "lor"
        ];

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $this->assertSucces($response, [
            "total" => 4
        ], collect([$lorena, $lorem, $lord, $lorem2]));
    }

    public function test_filter_by_nombre_completo()
    {
        $login = $this->getSuperUser();

        $lorena = Medico::factory()->state([
            "nombre" => "Lorena",
            "apellido_materno" => "Gomez",
            "apellido_paterno" => "Fulanito"
        ])->create();
        $lorem =  Medico::factory()->state([
            "nombre" => "Carla Lorena",
            "apellido_materno" => "Sanchez",
            "apellido_paterno" => "Fulanito"
        ])->create();
        $lord = Medico::factory()->state([
            "nombre" => "Juan",
            "apellido_materno" => "Kelvin",
            "apellido_paterno" => "Gómez",
            "especialidad" => "Cirujano"
        ])->create();

        Medico::factory()->state([
            "nombre" => "Cosme",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Fulanito"
        ])->create();

        DB::commit();
        RefreshDatabaseState::$migrated = false;
        $this->beforeApplicationDestroyed(function(){
            $this->refreshDatabase();
            // Medico::truncate();
        });

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => ["nombre" => "lorena"]
        ]));
        $this->assertSucces($response, [
            "total" => 2
        ], collect([$lorena, $lorem]));

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => ["nombre" => "gomez"]
        ]));
        $this->assertSucces($response, [
            "total" => 2
        ], collect([$lorena, $lord]));
    }

    public function test_filter_by_ci()
    {
        $login = $this->getSuperUser();

        $medico = Medico::factory()->create();
        Medico::factory()->count(10)->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "ci" => $medico->ci->toArray()
        ];

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $this->assertSucces($response, [
            "total" => 1
        ], collect([$medico]));
    }

    public function test_filter_by_especialidad()
    {
        $login = $this->getSuperUser();

        $medico = Medico::factory([
            "especialidad" => "Oncología"
        ])->create();

        $medico2 = Medico::factory([
            "especialidad" => "Neuro-oncologia"
        ])->create();

        Medico::factory([
            "especialidad" => "Nefrología"
        ])->create();

        DB::commit();
        RefreshDatabaseState::$migrated = false;
        $this->beforeApplicationDestroyed(function(){
            $this->refreshDatabase();
            // Medico::truncate();
        });


        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "especialidad" => "oncología"
        ];

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $this->assertSucces($response, [
            "total" => 2
        ], collect([$medico, $medico2]));
    }

    public function test_filter_by_estado()
    {
        $login = $this->getSuperUser();

        $activo = Medico::factory()->create();
        $baja = Medico::factory()->baja()->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "estado" => 1
        ];

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $this->assertSucces($response, [
            "total" => 1
        ], collect([$activo]));
        
        $filter = [
            "estado" => 2
        ];

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $this->assertSucces($response, [
            "total" => 1
        ], collect([$baja]));

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page
        ]));
        $this->assertSucces($response,[
            "total" => 2
        ],collect([$activo, $baja]));
    }

    public function test_filter_by_regional()
    {
        $login = $this->getSuperUser();

        $medicoLaPaz = Medico::factory()->regionalLaPaz()->create();
        $medicoSantaCruz = Medico::factory()->regionalSantaCruz()->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => [
                "regional_id" => $medicoLaPaz->regional_id
            ]
        ]));
        $this->assertSucces($response,[
            "total" => 1
        ], collect([$medicoLaPaz]));

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page,
            "filter" => [
                "regional_id" => $medicoSantaCruz->regional_id
            ]
        ]));
        $this->assertSucces($response,[
            "total" => 1
        ], collect([$medicoSantaCruz]));

        $response = $this->actingAs($login)->getJson("/api/medicos?".http_build_query([
            "page" => $page
        ]));
        $this->assertSucces($response,[
            "total" => 2
        ], collect([$medicoLaPaz, $medicoSantaCruz]));
    }
}
