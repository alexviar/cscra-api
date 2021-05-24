<?php

namespace Tests\Feature;

use App\Models\ContratoProveedor;
use App\Models\Especialidad;
use App\Models\Galeno\AfiliacionBeneficiario;
use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\Prestacion;
use App\Models\Proveedor;
use App\Models\Regional;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SolicitudAtencionExternaTest extends TestCase
{


    function createSuperUser(){
        $user = User::factory()
            ->state([
                "regional_id" => 1
            ])
            ->create();
        $user->syncRoles([
            "super user"
        ]);

        return $user;
    }

    function solicitudValida(){
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()
            ->for($titular)
            ->for($empleador)
            ->create();
        
        $medico = Medico::factory()
            ->state([
                "regional_id" => 1
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()
            ->state([
                "regional_id" => 1
            ])
            ->has(
                ContratoProveedor::factory()
                ->has(Prestacion::factory()->count(10), "prestaciones")
                ->inicioAyer()
            , "contratos")
            ->create();
        $data = [
            "asegurado_id" => $titular->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(fn ($prestacion) => [
                "prestacion_id" => $prestacion->id
            ])
        ];

        yield $data;

        // $beneficiario = Afiliado::factory()->beneficiario()
        //     ->has(
        //         AfiliacionBeneficiario::factory()
        //         ->for($afiliacionTitular)
        //     )->create();

        // $data["asegurado_id"] = $beneficiario->id;

        // yield $data;
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testSolicitudValida()
    {
        // $this->seed();
        $this->travelTo(Carbon::create(2020));

        DB::beginTransaction();
        DB::connection("galeno")->beginTransaction();

        $user = $this->createSuperUser();

        $iterator = $this->solicitudValida();

        $data = $iterator->current();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertStatus(200);

        // $rol = Role::factory()->create();
        // $rol->syncPermissions([
        //     Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA
        // ]);
        // $user = User::factory()
        //     ->state([
        //         "regional_id" => 1
        //     ])
        //     ->for($rol)
        //     ->create();
        
        // $iterator->next();
        // $data = $iterator->current();

        // $response = $this->actingAs($user, "sanctum")->postJson('/solicitudes-atencion-externa', $data);
        // $response->assertStatus(200);

        DB::rollBack();
        DB::connection("galeno")->rollback();
    }

    function test_beneficiario_con_fecha_de_extinsion_vencida_ayer(){
        $this->travelTo(Carbon::create(2020));

        DB::beginTransaction();
        DB::connection("galeno")->beginTransaction();

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
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                ->has(Prestacion::factory()->count(10), "prestaciones")
                ->inicioAyer()
            , "contratos")
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(fn ($prestacion) => [
                "prestacion_id" => $prestacion->id
            ])
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]); 
        
        DB::rollBack();
        DB::connection("galeno")->rollback();
    }
    

    function test_beneficiario_con_fecha_de_extinsion_vencida_hoy(){
        $this->travelTo(Carbon::create(2020));

        DB::beginTransaction();
        DB::connection("galeno")->beginTransaction();

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

        $proveedor = Proveedor::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                ->has(Prestacion::factory()->count(10), "prestaciones")
                ->inicioAyer()
            , "contratos")
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(fn ($prestacion) => [
                "prestacion_id" => $prestacion->id
            ])
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.fecha_extincion" => "Fecha de extincion alcanzada"]); 
        
        DB::rollBack();
        DB::connection("galeno")->rollback();
    }
    
    function test_beneficiario_con_fecha_de_extinsion_vencida_maniana(){
        $this->travelTo(Carbon::create(2020));

        DB::beginTransaction();
        DB::connection("galeno")->beginTransaction();

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
        // dd($afiliacionBeneficiario);
        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                ->has(Prestacion::factory()->count(10), "prestaciones")
                ->inicioAyer()
            , "contratos")
            ->create();

        $data = [
            "asegurado_id" => $beneficiario->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(fn ($prestacion) => [
                "prestacion_id" => $prestacion->id
            ])
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk(); 
        
        DB::rollBack();
        DB::connection("galeno")->rollback();
    }  
    
    function test_afiliado_con_estado_desconocido(){
        $this->travelTo(Carbon::create(2020));

        DB::beginTransaction();
        DB::connection("galeno")->beginTransaction();

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->state(["ESTADO_AFI" => 0])->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                ->has(Prestacion::factory()->count(10), "prestaciones")
                ->inicioAyer()
            , "contratos")
            ->create();

        $data = [
            "asegurado_id" => $titular->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(fn ($prestacion) => [
                "prestacion_id" => $prestacion->id
            ])
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.estado" => "El asegurado tiene un estado indeterminado"]);
        
        $titular = Afiliado::factory()->state(["ESTADO_AFI" => 3])->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();
        
        $data = [
            "asegurado_id" => $titular->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(fn ($prestacion) => [
                "prestacion_id" => $prestacion->id
            ])
        ];
        
        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertJsonValidationErrors(["asegurado.estado" => "El asegurado tiene un estado indeterminado"]);
        

        DB::rollBack();
        DB::connection("galeno")->rollback();
    }
    
    function test_beneficiario_con_fecha_de_validez_vencida_ayer(){
        $this->travelTo(Carbon::create(2020));

        DB::beginTransaction();
        DB::connection("galeno")->beginTransaction();

        $regional_id = 1;
        $empleador = Empleador::factory()->create();
        $titular = Afiliado::factory()->create();
        $afiliacionTitular = AfiliacionTitular::factory()->for($titular)->for($empleador)->create();

        $medico = Medico::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->for(Especialidad::factory()->create())
            ->create();

        $proveedor = Proveedor::factory()
            ->state([
                "regional_id" => $regional_id
            ])
            ->has(
                ContratoProveedor::factory()
                ->has(Prestacion::factory()->count(10), "prestaciones")
                ->inicioAyer()
            , "contratos")
            ->create();

        $data = [
            "asegurado_id" => $titular->id,
            "regional_id" => 1,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "prestaciones_solicitadas" => $proveedor->contrato->prestaciones->random(1)->map(fn ($prestacion) => [
                "prestacion_id" => $prestacion->id
            ])
        ];

        $user = $this->createSuperUser();

        $response = $this->actingAs($user, "sanctum")->postJson('/api/solicitudes-atencion-externa', $data);
        $response->assertOk(); 
        
        DB::rollBack();
        DB::connection("galeno")->rollback();
    }
}
