<?php

namespace Tests\Feature\SolicitudAtencionExterna;

use App\Models\Galeno\AfiliacionBeneficiario;
use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\Proveedor;
use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BuscarSolicitudAtencionExternaTest extends TestCase
{
    use WithFaker;

    protected $connectionsToTransact = ["mysql", "galeno"];

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
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA
            ])
            ->create();

        for ($i = 0; $i < 20; $i++) SolicitudAtencionExterna::factory()
            
            ->create();

        $response = $this->actingAs($login)
            ->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
                "page" => ["size" => 10]
            ]));

        $this->assertSuccess($response, [
            "total" => 20,
            "nextPage" => 2
        ], SolicitudAtencionExterna::limit(10)->get());
    }

    public function test_usuario_puede_buscar_solo_dentro_de_su_regional()
    {
        $solicitudLaPaz = SolicitudAtencionExterna::factory()->regionalLaPaz()->create();
        $solicitudSantaCruz = SolicitudAtencionExterna::factory()->regionalSantaCruz()->create();

        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10]
        ]));
        $response->assertForbidden();

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["regional_id" => 3]
        ]));
        $response->assertForbidden();

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["regional_id" => 1]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$solicitudLaPaz]));

        $login = User::factory()
            ->regionalSantaCruz()
            ->withPermissions([
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10]
        ]));
        $response->assertForbidden();

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["regional_id" => 1]
        ]));
        $response->assertForbidden();

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["regional_id" => 3]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$solicitudSantaCruz]));

        $login = User::factory()
            ->regionalSantaCruz()
            ->withPermissions([
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA,
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10]
        ]));
        $response->assertForbidden();
    }

    public function test_buscar_por_proveedor()
    {
        $login = $this->getSuperUser();

        $thisNot = SolicitudAtencionExterna::factory()->create();
        $returnThis = SolicitudAtencionExterna::factory()->create();
        $thisAnotherNot = SolicitudAtencionExterna::factory()->create();

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10]
        ]));

        $this->assertSuccess($response, [
            "total" => 3,
        ], collect([$thisNot, $returnThis, $thisAnotherNot]));

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["proveedor_id" => $returnThis->proveedor->id]
        ]));

        $this->assertSuccess($response, [
            "total" => 1,
        ], collect([$returnThis]));
    }

    public function test_buscar_por_medico()
    {
        $login = $this->getSuperUser();

        $thisNot = SolicitudAtencionExterna::factory()->create();
        $returnThis = SolicitudAtencionExterna::factory()->create();
        $thisAnotherNot = SolicitudAtencionExterna::factory()->create();

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10]
        ]));

        $this->assertSuccess($response, [
            "total" => 3,
        ], collect([$thisNot, $returnThis, $thisAnotherNot]));

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["medico_id" => $returnThis->medico->id]
        ]));

        $this->assertSuccess($response, [
            "total" => 1,
        ], collect([$returnThis]));
    }

    public function test_buscar_por_regional()
    {
        $login = $this->getSuperUser();

        $thisNot = SolicitudAtencionExterna::factory()->regionalLaPaz()->create();
        $returnThis = SolicitudAtencionExterna::factory()->regionalSantaCruz()->create();
        $thisAnotherNot = SolicitudAtencionExterna::factory()->regionalLaPaz()->create();

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10]
        ]));
        $this->assertSuccess($response, [
            "total" => 3,
        ], collect([$thisNot, $returnThis, $thisAnotherNot]));

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["regional_id" => 1]
        ]));
        $this->assertSuccess($response, [
            "total" => 2,
        ], collect([$thisNot, $thisAnotherNot]));

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["regional_id" => 3]
        ]));
        $this->assertSuccess($response, [
            "total" => 1,
        ], collect([$returnThis]));
    }

    public function test_buscar_por_afiliado()
    {
        $login = $this->getSuperUser();

        $thisNot = SolicitudAtencionExterna::factory()->create();
        $titular = Afiliado::factory()->titular()->create();
        $returnThis3 = SolicitudAtencionExterna::factory()->for($titular, "paciente")->create();
        $thisAnotherNot = SolicitudAtencionExterna::factory()->create();

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["matricula" => $titular->matricula]
        ]));
        $this->assertSuccess($response, [
            "total" => 1,
        ], collect([$returnThis3]));


        $beneficiario1 = AfiliacionBeneficiario::factory()->for($titular->afiliacion, "afiliacionDelTitular")->create()->afiliado;
        $beneficiario2 = AfiliacionBeneficiario::factory()->for($titular->afiliacion, "afiliacionDelTitular")->create()->afiliado;

        $returnThis1 = SolicitudAtencionExterna::factory()->for($beneficiario1, "paciente")->create();
        $returnThis2 = SolicitudAtencionExterna::factory()->for($beneficiario2, "paciente")->create();

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10]
        ]));
        $this->assertSuccess($response, [
            "total" => 5,
        ], collect([$thisNot, $returnThis3, $thisAnotherNot, $returnThis1, $returnThis2]));

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["matricula" => $beneficiario1->matricula]
        ]));
        $this->assertSuccess($response, [
            "total" => 1,
        ], collect([$returnThis1]));

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["matricula" => $beneficiario2->matricula]
        ]));
        $this->assertSuccess($response, [
            "total" => 1,
        ], collect([$returnThis2]));

        $response = $this->actingAs($login)->get("/api/solicitudes-atencion-externa?" . http_build_query([
            "page" => ["size" => 10],
            "filter" => ["matricula" => $beneficiario1->titular->matricula]
        ]));
        $this->assertSuccess($response, [
            "total" => 3,
        ], collect([$returnThis3, $returnThis1, $returnThis2]));
    }

    public function test_buscar_por_fechas()
    {
        $login = $this->getSuperUser();

        $desde = new CarbonImmutable($this->faker->dateTime());
        $hasta = $desde->addMonths(3);

        $antes = SolicitudAtencionExterna::factory([
            "fecha" => $desde->subDay(),
        ])->create();
        $between = [];
        for($i = 0; $i < 3; $i++) $between[] = SolicitudAtencionExterna::factory([
            "fecha" => $this->faker->dateTimeBetween($desde, $hasta),
        ])->create();
        $despues = SolicitudAtencionExterna::factory([
            "fecha" => $hasta->addDay()
        ])->create();

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?".http_build_query([
            "page" => ["size" => 10]
        ]));
        $this->assertSuccess($response, [
            "total" => 5
        ], collect(array_merge([$antes], $between, [$despues])));

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?".http_build_query([
            "page" => ["size" => 10],
            "filter" => ["desde" => $desde->format("Y-m-d")]
        ]));
        $this->assertSuccess($response, [
            "total" => 4
        ], collect(array_merge($between, [$despues])));

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?".http_build_query([
            "page" => ["size" => 10],
            "filter" => ["desde" => $hasta->addDay()->format("Y-m-d")]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$despues]));

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?".http_build_query([
            "page" => ["size" => 10],
            "filter" => ["hasta" => $desde->subDay()->format("Y-m-d")]
        ]));
        $this->assertSuccess($response, [
            "total" => 1
        ], collect([$antes]));

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?".http_build_query([
            "page" => ["size" => 10],
            "filter" => ["hasta" => $hasta->format("Y-m-d")]
        ]));
        $this->assertSuccess($response, [
            "total" => 4
        ], collect(array_merge([$antes], $between)));

        $response = $this->actingAs($login)->getJson("/api/solicitudes-atencion-externa?".http_build_query([
            "page" => ["size" => 10],
            "filter" => ["desde" => $desde->format("Y-m-d"), "hasta" => $hasta->format("Y-m-d")]
        ]));
        $this->assertSuccess($response, [
            "total" => 3
        ], collect($between));

    }

    public function test_usuario_sin_permiso()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/solicitudes-atencion-externa");
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->getJson("/api/solicitudes-atencion-externa");
        $response->assertUnauthorized();
    }
}
