<?php

namespace Tests\Feature\SolicitudAtencionExterna;

use App\Models\ContratoProveedor;
use App\Models\Especialidad;
use App\Models\Galeno\AfiliacionBeneficiario;
use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\AmpliacionPrestacion;
use App\Models\Galeno\BajaAfiliacion;
use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\Prestacion;
use App\Models\Proveedor;
use App\Models\Regional;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\Galeno\BajaAfiliacionFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistrarSolicitudAtencionExternaTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ["mysql", "galeno"];


    function createSuperUser()
    {
        return User::where("username", "admin")->first();
    }

    function assertSolicitudRegistrada(){
        $this->assertDatabaseCount("atenciones_externas", 1);
        $this->assertDatabaseCount("detalles_atenciones_externas", 1);
    }

    function test_beneficiario_con_fecha_de_extinsion_vencida_ayer()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->fechaExtinsionVencidaAyer()
            ->create();
        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]);
    }


    function test_beneficiario_con_fecha_de_extinsion_vencida_hoy()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->fechaExtinsionVencidaHoy()
            ->create();
        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]);
    }

    function test_beneficiario_con_fecha_de_extinsion_vencida_maniana()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->fechaExtinsionVencidaManiana()
            ->create();
            
        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }

    function test_beneficiario_sin_fecha_de_extinsion()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }

    function test_beneficiario_con_ampliacion_vencida_ayer()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->fechaExtinsionVencidaManiana()
            ->create();
        $ampliacionPrestacion = AmpliacionPrestacion::factory()
            ->for($afiliacionBeneficiario)
            ->vencidaAyer()
            ->create();
        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]);
    }

    function test_beneficiario_con_ampliacion_vencida_hoy()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->fechaExtinsionVencidaManiana()
            ->create();
        $ampliacionPrestacion = AmpliacionPrestacion::factory()
            ->for($afiliacionBeneficiario)
            ->vencidaHoy()
            ->create();
        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]);
    }

    function test_beneficiario_con_ampliacion_vencida_maniana()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->fechaExtinsionVencidaAyer()
            ->create();
        $ampliacionPrestacion = AmpliacionPrestacion::factory()
            ->for($afiliacionBeneficiario)
            ->vencidaManiana()
            ->create();
        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }

    function test_afiliado_con_estado_desconocido()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->state(["ESTADO_AFI" => 0])->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->state(["ESTADO_AFI" => 0])->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();
        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.estado" => "El asegurado tiene un estado indeterminado"]);
        $response->assertJsonValidationErrors(["titular.estado" => "El asegurado tiene un estado indeterminado"]);

        $titular = Afiliado::factory()->state(["ESTADO_AFI" => 3])->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->state(["ESTADO_AFI" => 3])->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.estado" => "El asegurado tiene un estado indeterminado"]);
        $response->assertJsonValidationErrors(["titular.estado" => "El asegurado tiene un estado indeterminado"]);
    }

    function test_afiliado_con_estado_de_alta_pero_con_registro_de_baja()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()
            ->for($titular)
            ->for($empleador)
            ->create();
        BajaAfiliacion::factory()
            ->for($afiliacionTitular, "afiliacionTitular")
            ->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();
        BajaAfiliacion::factory()
            ->for($afiliacionBeneficiario)
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.estado" => "El asegurado figura como activo, pero existe registro de su baja",
            "titular.estado" => "El asegurado figura como activo, pero existe registro de su baja"
        ]);
    }


    function test_afiliado_con_estado_de_baja_pero_sin_registro_de_baja()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()
            ->for($titular)
            ->for($empleador)
            ->create();
        $beneficiario = Afiliado::factory()->baja()->beneficiario()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.estado" => "El asegurado figura como dado de baja, pero no se enontraron registros de la baja",
            "titular.estado" => "El asegurado figura como dado de baja, pero no se enontraron registros de la baja"
        ]);
    }

    function test_afiliado_con_fecha_de_validez_vencida_ayer()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        BajaAfiliacion::factory()->validezVencidaAyer()
            ->for($afiliacionTitular)
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $titular->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.fecha_validez_seguro" => "El seguro ya no tiene validez",
        ]);
    }

    function test_beneficiario_con_fecha_de_validez_vencida_ayer()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->baja()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();
        BajaAfiliacion::factory()->validezVencidaAyer()
            ->for($afiliacionBeneficiario)
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.fecha_validez_seguro" => "El seguro ya no tiene validez",
        ]);
    }

    function test_titular_con_fecha_de_validez_vencida_ayer()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        BajaAfiliacion::factory()->validezVencidaAyer()
            ->for($afiliacionTitular)
            ->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "titular.fecha_validez_seguro" => "El seguro ya no tiene validez"
        ]);
    }

    function test_afiliado_con_fecha_de_validez_vencida_hoy()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        BajaAfiliacion::factory()->validezVencidaHoy()
            ->for($afiliacionTitular, "afiliacionTitular")
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $titular->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.fecha_validez_seguro" => "El seguro ya no tiene validez"
        ]);
    }

    function test_beneficiario_con_fecha_de_validez_vencida_hoy()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->baja()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();
        BajaAfiliacion::factory()->validezVencidaHoy()
            ->for($afiliacionBeneficiario)
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.fecha_validez_seguro" => "El seguro ya no tiene validez"
        ]);
    }

    function test_titular_con_fecha_de_validez_vencida_hoy()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        BajaAfiliacion::factory()->validezVencidaHoy()
            ->for($afiliacionTitular)
            ->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "titular.fecha_validez_seguro" => "El seguro ya no tiene validez"
        ]);
    }

    function test_afiliado_con_fecha_de_validez_vencida_maniana()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        BajaAfiliacion::factory()->validezVencidaManiana()
            ->for($afiliacionTitular)
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $titular->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }

    function test_beneficiario_con_fecha_de_validez_vencida_maniana()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->baja()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();
        BajaAfiliacion::factory()->validezVencidaManiana()
            ->for($afiliacionBeneficiario)
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return  [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }

    function test_titular_con_fecha_de_validez_vencida_maniana()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        BajaAfiliacion::factory()->validezVencidaManiana()
            ->for($afiliacionTitular)
            ->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return  [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }

    function test_afiliado_con_baja_sin_fecha_de_validez()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        BajaAfiliacion::factory()
            ->for($afiliacionTitular)
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $titular->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_validez_seguro" => "Fecha no especificada, se asume que el seguro ya no tiene validez"]);
    }

    function test_beneficiario_con_baja_sin_fecha_de_validez()
    {
        $this->travelTo(Carbon::create(2020));

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $beneficiario = Afiliado::factory()->beneficiario()->baja()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();
        BajaAfiliacion::factory()
            ->for($afiliacionBeneficiario)
            ->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return  [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_validez_seguro" => "Fecha no especificada, se asume que el seguro ya no tiene validez"]);
    }

    function test_titular_con_baja_sin_fecha_de_validez()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->baja()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        BajaAfiliacion::factory()
            ->for($afiliacionTitular)
            ->create();
        $beneficiario = Afiliado::factory()->beneficiario()->create();
        $afiliacionBeneficiario = AfiliacionBeneficiario::factory()
            ->for($beneficiario)
            ->for($afiliacionTitular, "afiliacionDelTitular")
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["titular.fecha_validez_seguro" => "Fecha no especificada, se asume que el seguro ya no tiene validez"]);
    }

    function test_empleador_dado_de_baja_sin_fecha_de_baja()
    {
        
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->bajaSinFecha()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["empleador.fecha_baja" => "No se ha especificado la fecha de baja, se asume que el seguro ya no tiene validez"]);
    }

    function test_empleador_dado_de_baja_hace_dos_meses_y_un_dia()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->bajaHace2MesesMas1Dia()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.fecha_baja" => "El seguro ya no tiene validez"
        ]);
    }
    
    function test_empleador_dado_de_baja_hace_dos_meses()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->bajaHace2Meses()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.fecha_baja" => "El seguro ya no tiene validez"
        ]);
    }
    
    function test_empleador_dado_de_baja_hace_dos_meses_menos_un_dia()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->bajaHace2MesesMenos1Dia()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }
    
    function test_empleador_de_alta_con_fecha_de_baja()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->altaConFechaBaja()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.estado" => "El empleador figura como activo, pero tiene una fecha de baja"
        ]);
    }
    
    function test_empleador_con_estado_desconocido()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->estadoDesconocido()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.estado" => "El empleador tiene un estado indeterminado"
        ]);
    }

    public function test_empleador_en_mora()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();

        ListaMoraItem::create([
            "empleador_id" => $empleador->id,
            "numero_patronal" => $empleador->numero_patronal,
            "nombre" => $empleador->nombre,
            "regional_id" => Regional::mapGalenoIdToLocalId($empleador->regional_id)
        ]);

        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.aportes" => "El empleador esta en mora"
        ]);
    }

    public function test_medico_no_existe()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();

        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => 0,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "medico" => "El m??dico no existe"
        ]);
    }

    public function test_medico_pertenece_a_otra_regional()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "medico" => "El m??dico pertenece a otra regional"
        ]);
    }
    
    public function test_proveedor_no_existe()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $prestaciones = Prestacion::factory()->count(10)->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => 0,
            "prestaciones_solicitadas" => $prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "proveedor" => "El proveedor no existe"
        ]);
    }
    
    public function test_proveedor_pertenece_a_otra_regional()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalSantaCruz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(function ($prestacion) {
                return [
                    "prestacion_id" => $prestacion->id
                ];
            })
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "proveedor" => "El proveedor pertenece a otra regional"
        ]);
    }
    
    
    public function test_proveedor_no_ofrece_las_prestaciones_solicitadas()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $prestacion = Prestacion::factory()->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => [
                [
                    "prestacion_id" => $prestacion->id
                ]
            ]
        ];


        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "prestaciones_solicitadas.0.prestacion" => "El proveedor no ofrece esta prestacion"
        ]);
    }

    public function test_proveedor_con_contrato_consumido()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->create();

        $contrato = ContratoProveedor::factory()
            ->consumido()
            ->inicioAyer()
            ->indefinido()
            ->for($proveedor)
            ->has(Prestacion::factory()->count(10), "prestaciones")
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $contrato->prestaciones->random(1)
                ->map(function ($prestacion) {
                    return[
                        "prestacion_id" => $prestacion->id,
                        "nota" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                    ];
                })
        ];


        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "proveedor" => "El proveedor no tiene un contrato activo"
        ]);
    }    
    
    public function test_prestacion_no_existe()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $prestacionId = 1;
        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                                    // ->state(new Sequence(
                                    //     fn () => ['id' => $prestacionId++],
                                    // )), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => [
                [
                    "prestacion_id" => 0
                ]
            ]
        ];


        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "prestaciones_solicitadas.0.prestacion" => "La prestaci??n no existe"
        ]);
    }
    
    public function test_nota_con_mas_de_60_caracteres()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $prestacionId = 1;
        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                                    // ->state(new Sequence(
                                    //     fn () => ['id' => $prestacionId++],
                                    // )), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)
                ->map(function ($prestacion) {
                    return [
                        "prestacion_id" => $prestacion->id,
                        "nota" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis"
                    ];
                })
        ];


        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "prestaciones_solicitadas.0.nota" => "Las notas no deben exceder los 60 caracteres"
        ]);
    }
    
    public function test_usuario_con_permiso_para_registrar()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalSantaCruz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 3,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)
                ->map(function ($prestacion) {
                    return [
                        "prestacion_id" => $prestacion->id,
                    ];
                })
        ];

        $user = User::factory()
        ->withPermissions([
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA
        ])
        ->create();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }    
    
    public function test_usuario_con_permiso_para_registrar_por_regional()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalSantaCruz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 3,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)
                ->map(function ($prestacion) {
                    return [
                        "prestacion_id" => $prestacion->id,
                    ];
                })
        ];

        $user = User::factory()
        ->regionalSantaCruz()
        ->withPermissions([
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
        ])
        ->create();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }    
    
    public function test_usuario_con_permiso_para_registrar_por_regional_registrando_en_otra_regional()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalSantaCruz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 3,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)
                ->map(function ($prestacion) {
                    return [
                        "prestacion_id" => $prestacion->id,
                    ];
                })
        ];

        $user = User::factory()
        ->withPermissions([
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
        ])
        ->create();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertForbidden();
    }

    
    public function test_usuario_con_permiso_para_registrar_por_regional_y_global()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalSantaCruz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 3,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)
                ->map(function ($prestacion) {
                    return [
                        "prestacion_id" => $prestacion->id,
                    ];
                })
        ];

        $user = User::factory()
        ->withPermissions([
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
        ])
        ->withPermissions([
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA
        ])
        ->create();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk();
        $this->assertSolicitudRegistrada();
    }
    
    public function test_usuario_sin_permisos()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)
                ->map(function ($prestacion) {
                    return [
                        "prestacion_id" => $prestacion->id,
                    ];
                })
        ];

        $user = User::factory()
        ->regionalSantaCruz()
        ->withPermissions([])
        ->create();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertForbidden();
    }
    
    
    public function test_usuario_no_autenticado()
    {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()->empresa()
            ->regionalLaPaz()
            ->has(
                ContratoProveedor::factory()
                    ->has(Prestacion::factory()->count(10), "prestaciones")
                    ->inicioAyer(),
                "contratos"
            )
            ->create();

        $data = [
            "asegurado_id" => $asegurado->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)
                ->map(function ($prestacion) {
                    return [
                        "prestacion_id" => $prestacion->id,
                    ];
                })
        ];

        $response = $this->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertUnauthorized();
    }
}
