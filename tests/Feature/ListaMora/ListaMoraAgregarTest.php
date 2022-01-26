<?php

namespace Tests\Feature\ListaMora;

use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Permisos;
use App\Models\Regional;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ListaMoraAgregarTest extends TestCase
{
    protected $connectionsToTransact = ["mysql", "galeno"];

    private function assertSuccess(TestResponse $response, $empleador)
    {
        $response->assertOk();
        $this->assertDatabaseHas("lista_mora", [
            "empleador_id" => $empleador->id,
            "numero_patronal" => $empleador->numero_patronal,
            "nombre" => $empleador->nombre,
            "regional_id" => $empleador->regional_id,
        ]);
    }

    public function test_campos_requeridos()
    {
        $login = $this->getSuperUser();

        $response = $this->actingAs($login)
            ->postJson("/api/lista-mora", []);
        $response->assertJsonValidationErrors([
            "empleador_id" => "Este campo es requerido."
        ]);
    }    

    public function test_empleador_repetido()
    {
        $login = $this->getSuperUser();

        $item = ListaMoraItem::factory()->create();

        $response = $this->actingAs($login)
            ->postJson("/api/lista-mora", [
                "empleador_id" => $item->empleador_id
            ]);
        $response->assertJsonValidationErrors([
            "empleador_id" => "El empleador ya se encuentra en la lista de mora"
        ]);
    }

    public function test_empleador_no_existe()
    {
        $login = $this->getSuperUser();

        $response = $this->actingAs($login)
            ->postJson("/api/lista-mora", [
                "empleador_id" => "lkasfdñlkajs"
            ]);
        $response->assertJsonValidationErrors([
            "empleador_id" => "El empleador no existe."
        ]);
    }

    public function test_usuario_puede_registrar()
    {
        $login = User::factory()
            ->withPermissions([
                Permisos::AGREGAR_A_LA_LISTA_DE_MORA
            ])
            ->create();

        $empleadorLaPaz = Empleador::factory()
            ->regionalLaPaz()
            ->create();
            
        $empleadorSantaCruz = Empleador::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($login)
            ->postJson("/api/lista-mora", [
                "empleador_id" => $empleadorLaPaz->id
            ]);
        $this->assertSuccess($response, $empleadorLaPaz);

        $response = $this->actingAs($login)
            ->postJson("/api/lista-mora", [
                "empleador_id" => $empleadorSantaCruz->id
            ]);
        $this->assertSuccess($response, $empleadorSantaCruz);
    }
    
    public function test_usuario_puede_registrar_solo_dentro_de_su_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::AGREGAR_A_LA_LISTA_DE_MORA_MISMA_REGIONAL
            ])
            ->create();

        $empleadorLaPaz = Empleador::factory()
            ->regionalLaPaz()
            ->create();
            
        $empleadorSantaCruz = Empleador::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($login)
            ->postJson("/api/lista-mora", [
                "empleador_id" => $empleadorLaPaz->id
            ]);
        $this->assertSuccess($response, $empleadorLaPaz);

        $response = $this->actingAs($login)
            ->postJson("/api/lista-mora", [
                "empleador_id" => $empleadorSantaCruz->id
            ]);
        $response->assertForbidden();

        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::AGREGAR_A_LA_LISTA_DE_MORA,
                Permisos::AGREGAR_A_LA_LISTA_DE_MORA_MISMA_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($login)
            ->postJson("/api/lista-mora", [
                "empleador_id" => $empleadorSantaCruz->id
            ]);
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();
    
        $empleador = Empleador::factory()->create();
        
        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/lista-mora', [
                "empleador_id" => $empleador->id
            ]);
        $response->assertForbidden();
    }    

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $empleador = Empleador::factory()->create();

        $response = $this->actingAs($login)
            ->postJson('/api/lista-mora', [
                "empleador_id" => $empleador->id
            ]);
        $this->assertSuccess($response, $empleador);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::AGREGAR_A_LA_LISTA_DE_MORA])
            ->create();

        $empleador = Empleador::factory()->create();

        $response = $this->actingAs($login)
            ->postJson('/api/lista-mora', [
                "empleador_id" => $empleador->id
            ]);
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->postJson('/api/lista-mora', []);
        $response->assertUnauthorized();
    }
}
