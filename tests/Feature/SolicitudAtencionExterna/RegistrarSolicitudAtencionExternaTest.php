<?php

namespace Tests\Feature\SolicitudAtencionExterna;

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
use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RegistrarSolicitudAtencionExternaTest extends TestCase
{
    use WithFaker;

    protected $connectionsToTransact = ["mysql", "galeno"];

    function setUp(): void
    {
        parent::setUp();
        Storage::fake("local");
    }

    function assertSuccess(TestResponse $response, $data){
        $response->assertOk();
    }

    function test_beneficiario_con_fecha_de_extinsion_vencida()
    {
        $login = $this->getSuperUser();

        $beneficiario = AfiliacionBeneficiario::factory()->noExtinguible()->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($beneficiario, "paciente")->for($login, "usuario")->raw();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["asegurado.fecha_extincion"]);

        $beneficiario = Afiliado::factory()->beneficiario(AfiliacionBeneficiario::factory()->extinguible())->create();
        $data = SolicitudAtencionExterna::factory()->for($beneficiario, "paciente")->for($login, "usuario")->raw();

        $fechaExtinsion = $beneficiario->afiliacion->fecha_extincion->clone();
        $this->travelTo($fechaExtinsion->subDay());
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["asegurado.fecha_extincion"]);

        $this->travel(1)->days();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]);

        $this->travel(1)->days();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]);

        $fechaExtinsion = $beneficiario->afiliacion->fecha_extincion->clone();
        $ampliacion=$fechaExtinsion->addYears(5);
        AmpliacionPrestacion::factory()->for($beneficiario->afiliacion)->vencimiento($ampliacion)->create();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["asegurado.fecha_extincion"]);

        $this->travelTo($ampliacion->subDay());
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["asegurado.fecha_extincion"]);

        $this->travel(1)->days();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]);

        $this->travel(1)->days();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]);

        $this->travelBack();
    }

    function test_afiliado_con_estado_desconocido()
    {
        $login = $this->getSuperUser();

        $paciente = Afiliado::factory()->titular()->estadoDesconocido();
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();

        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.estado" => "El asegurado tiene un estado indeterminado"]);
        
        $titular = Afiliado::factory()->titular()->estadoDesconocido()->create();
        $paciente = AfiliacionBeneficiario::factory()->for($titular->afiliacion, "afiliacionDelTitular")->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        // var_dump($paciente->titular->getAttributes(), $titular->getAttributes());

        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["titular.estado" => "El asegurado tiene un estado indeterminado"]);
    }

    function test_afiliado_con_estado_de_alta_pero_con_registro_de_baja()
    {
        $user = $this->getSuperUser();

        $afiliacion = AfiliacionBeneficiario::factory()->create();
        $data = SolicitudAtencionExterna::factory()->for($afiliacion->afiliado, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["asegurado.estado"]);

        $baja = BajaAfiliacion::factory()->for($afiliacion)->create();
        // dd($baja->getAttributes(), $afiliacion->getAttributes());
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.estado" => "El asegurado figura como activo, pero existe registro de su baja"
        ]);

        BajaAfiliacion::factory()->for($afiliacion->afiliacionDelTitular)->create();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.estado" => "El asegurado figura como activo, pero existe registro de su baja",
            "titular.estado" => "El asegurado figura como activo, pero existe registro de su baja"
        ]);
    }


    function test_afiliado_con_estado_de_baja_pero_sin_registro_de_baja()
    {
        $user = $this->getSuperUser();

        $afiliado = Afiliado::factory()->beneficiario()->baja()->create();
        $data = SolicitudAtencionExterna::factory()->for($afiliado, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.estado" => "El asegurado figura como dado de baja, pero no se enontraron registros de la baja",
        ]);

        $titular = Afiliado::factory()->titular()->baja()->create();
        $paciente = AfiliacionBeneficiario::factory()->for($titular->afiliacion, "afiliacionDelTitular")->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "titular.estado" => "El asegurado figura como dado de baja, pero no se enontraron registros de la baja"
        ]);
    }

    function test_beneficiario_con_fecha_de_validez_vencida()
    {
        $login = $this->getSuperUser();

        $beneficiario = AfiliacionBeneficiario::factory()->noExtinguible()->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($beneficiario, "paciente")->for($login, "usuario")->raw();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["asegurado.fecha_validez_seguro", "titular.fecha_validez_seguro"]);

        $baja = BajaAfiliacion::factory()->for($beneficiario->afiliacion)->create();
        $vencimiento = $baja->fecha_validez_seguro;
        $this->travelTo($vencimiento->subDay());
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["asegurado.fecha_validez_seguro"]);

        $this->travel(1)->days();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_validez_seguro" => "El seguro ya no tiene validez"]);

        $this->travel(1)->days();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_validez_seguro" => "El seguro ya no tiene validez"]);

        $baja->delete();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["asegurado.fecha_validez_seguro", "titular.fecha_validez_seguro"]);
        
        $baja = BajaAfiliacion::factory()->for($beneficiario->titular->afiliacion)->create();
        $vencimiento = $baja->fecha_validez_seguro;
        $this->travelTo($vencimiento->subDay());
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["titular.fecha_validez_seguro"]);

        $this->travel(1)->days();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["titular.fecha_validez_seguro" => "El seguro ya no tiene validez"]);

        $this->travel(1)->days();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["titular.fecha_validez_seguro" => "El seguro ya no tiene validez"]);


        $this->travelBack();
    }

    function test_afiliado_con_baja_sin_fecha_de_validez()
    {
        $user = $this->getSuperUser();

        $beneficiario = Afiliado::factory()->beneficiario()->baja()->create();
        BajaAfiliacion::factory()->for($beneficiario->afiliacion)->sinVencimiento()->create();
        $data = SolicitudAtencionExterna::factory()->for($beneficiario, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "asegurado.fecha_validez_seguro" => "Fecha no especificada, se asume que el seguro ya no tiene validez"
        ]);

        $titular = Afiliado::factory()->titular()->baja()->create();
        BajaAfiliacion::factory()->for($titular->afiliacion)->sinVencimiento()->create();
        $paciente = AfiliacionBeneficiario::factory()->for($titular->afiliacion, "afiliacionDelTitular")->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "titular.fecha_validez_seguro" => "Fecha no especificada, se asume que el seguro ya no tiene validez"
        ]);
    }

    function test_empleador_dado_de_baja_sin_fecha_de_baja()
    {
        $user = $this->getSuperUser();
        
        $paciente = (rand(0,1) ? Afiliado::factory()->beneficiario() : Afiliado::factory()->beneficiario())->create();
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["empleador.fecha_baja"]);

        $paciente = AfiliacionTitular::factory()->for(Empleador::factory()->baja(false))->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.fecha_baja" => "No se ha especificado la fecha de baja, se asume que el seguro ya no tiene validez"
        ]);
        
        $paciente = AfiliacionBeneficiario::factory()->for($paciente->afiliacion, "afiliacionDelTitular")->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.fecha_baja" => "No se ha especificado la fecha de baja, se asume que el seguro ya no tiene validez"
        ]);
    }

    function test_empleador_dado_de_baja()
    {
        $user = $this->getSuperUser();

        $paciente = AfiliacionTitular::factory()->for(Empleador::factory()->baja())->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();

        $fecha_baja = $paciente->empleador->fecha_baja->clone();
        $this->travelTo($fecha_baja->addMonths(2)->subDay());
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["empleador.fecha_baja"]);

        $this->travel(1)->days();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.fecha_baja" => "El seguro ya no tiene validez"
        ]);

        $this->travel(1)->days();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.fecha_baja" => "El seguro ya no tiene validez"
        ]);
        
        $paciente = AfiliacionBeneficiario::factory()->for($paciente->afiliacion, "afiliacionDelTitular")->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();

        $fecha_baja = $paciente->empleador->fecha_baja->clone();
        $this->travelTo($fecha_baja->addMonths(2)->subDay());
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["empleador.fecha_baja"]);

        $this->travel(1)->days();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.fecha_baja" => "El seguro ya no tiene validez"
        ]);
        
        $this->travel(1)->days();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.fecha_baja" => "El seguro ya no tiene validez"
        ]);
    }
    
    function test_empleador_con_estado_desconocido()
    {

        $user = $this->getSuperUser();

        $paciente = AfiliacionTitular::factory()->for(Empleador::factory()->estadoDesconocido())->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.estado" => "El empleador tiene un estado indeterminado"
        ]);
        
        $paciente = AfiliacionBeneficiario::factory()->for($paciente->afiliacion, "afiliacionDelTitular")->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.estado" => "El empleador tiene un estado indeterminado"
        ]);
    }

    public function test_empleador_en_mora()
    {
        $user = $this->getSuperUser();

        $paciente = AfiliacionTitular::factory()->for(Empleador::factory()->estadoDesconocido())->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonMissingValidationErrors(["empleador.aportes"]);

        ListaMoraItem::factory()->for($paciente->empleador)->create();
        
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.aportes" => "El empleador esta en mora"
        ]);
        
        $paciente = AfiliacionBeneficiario::factory()->for($paciente->afiliacion, "afiliacionDelTitular")->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "empleador.aportes" => "El empleador esta en mora"
        ]);
    }

    public function test_medico_no_existe()
    {
        $user = $this->getSuperUser();

        $data = SolicitudAtencionExterna::factory([
            "medico_id" => 0
        ])->raw();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "medico" => "El médico no existe"
        ]);
    }

    public function test_medico_pertenece_a_otra_regional()
    {
        $user = $this->getSuperUser();

        $medico = Medico::factory()->regionalLaPaz()->create();

        $data = SolicitudAtencionExterna::factory()->regionalSantaCruz()->for($medico)->raw();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "medico" => "El médico pertenece a otra regional"
        ]);
    }
    
    public function test_proveedor_no_existe()
    {
        $login = $this->getSuperUser();

        $data = SolicitudAtencionExterna::factory([
            "proveedor_id" => 0
        ])->raw();

        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "proveedor" => "El proveedor no existe"
        ]);
    }
    
    public function test_proveedor_pertenece_a_otra_regional()
    {
        $user = $this->getSuperUser();

        $proveedor = Proveedor::factory()->regionalLaPaz()->create();

        $data = SolicitudAtencionExterna::factory()->regionalSantaCruz()->for($proveedor)->raw();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors([
            "proveedor" => "El proveedor pertenece a otra regional"
        ]);
    }

    function test_derechohabiente()
    {
        $user = $this->getSuperUser();

        $derechohabiente = AfiliacionBeneficiario::factory()->derechohabiente()->create()->afiliado;
        BajaAfiliacion::factory()->for($derechohabiente->titular->afiliacion)->create();
        $data = SolicitudAtencionExterna::factory()->for($derechohabiente, "paciente")->raw();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $this->assertSuccess($response, $data);
    }
    
    public function test_usuario_puede_registrar()
    {
        $user = User::factory()
        ->regionalLaPaz()
        ->withPermissions([
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA
        ])
        ->create();

        $paciente = AfiliacionBeneficiario::factory()->noExtinguible()->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->regionalLaPaz()->for($paciente, "paciente")->raw();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $this->assertSuccess($response, $data);
        
        $data = SolicitudAtencionExterna::factory()->regionalSantaCruz()->for($paciente, "paciente")->raw();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $this->assertSuccess($response, $data);
    }   

    public function test_usuario_puede_registrar_solo_dentro_de_su_regional()
    {
        $login = User::factory()
        ->regionalLaPaz()
        ->withPermissions([
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
        ])
        ->create();

        $paciente = AfiliacionBeneficiario::factory()->noExtinguible()->create()->afiliado;
        $data = SolicitudAtencionExterna::factory()->regionalLaPaz()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $this->assertSuccess($response, $data);
        
        $data = SolicitudAtencionExterna::factory()->regionalSantaCruz()->for($paciente, "paciente")->raw();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertForbidden();

        $login = User::factory()
        ->regionalLaPaz()
        ->withPermissions([
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA,
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
        ])
        ->create();
        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertForbidden();
    }
    
    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();

        $data = SolicitudAtencionExterna::factory()->raw();

        $response = $this->actingAs($login, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertForbidden();
    }
    
    public function test_usuario_no_autenticado()
    {
        $response = $this->postJson('/api/solicitudes-atencion-externa', []);
        $response->assertUnauthorized();
    }
}
