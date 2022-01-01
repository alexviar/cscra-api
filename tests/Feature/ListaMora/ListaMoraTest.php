<?php

namespace Tests\Feature\ListaMora;

use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ListaMoraTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_campos_requeridos()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/agregar", []);
        $response->assertJsonValidationErrors([
            "empleador_id" => "Este campo es requerido."
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/quitar", []);
        $response->assertJsonValidationErrors([
            "empleador_id" => "Este campo es requerido."
        ]);
    }

    public function test_usuario_con_permiso_para_agregar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::AGREGAR_EMPLEADOR_EN_MORA
            ])
            ->create();
        $empleadorLaPaz = Empleador::factory()
            ->regionalLaPaz()
            ->create();
            
        $empleadorSantaCruz = Empleador::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/agregar", [
                "empleador_id" => $empleadorLaPaz->id
            ]);

        $response->assertOk();
        $this->assertDatabaseHas("lista_mora", [
            "empleador_id" => $empleadorLaPaz->id
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/agregar", [
                "empleador_id" => $empleadorSantaCruz->id
            ]);

        $response->assertOk();
        $this->assertDatabaseHas("lista_mora", [
            "empleador_id" => $empleadorSantaCruz->id
        ]);
    }
    
    /**
     * 
     * @depends test_usuario_con_permiso_para_agregar
     */
    public function test_usuario_con_permiso_para_quitar($items)
    {
        // [$empleadorLaPaz, $empleadorSantaCruz] = $items;
        $user = User::factory()
            ->withPermissions([
                Permisos::QUITAR_EMPLEADOR_EN_MORA
            ])
            ->create();

            $empleadorLaPaz = Empleador::factory()
            ->regionalLaPaz()
            ->create();
        ListaMoraItem::factory()
            ->regionalLaPaz()
            ->for($empleadorLaPaz)
            ->state([
                "nombre" => $empleadorLaPaz->nombre,
                "numero_patronal" => $empleadorLaPaz->numero_patronal,
            ])
            ->create();
        $empleadorSantaCruz = Empleador::factory()
        ->regionalSantaCruz()
        ->create();
        ListaMoraItem::factory()
            ->regionalSantaCruz()
            ->for($empleadorSantaCruz)
            ->state([
                "nombre" => $empleadorSantaCruz->nombre,
                "numero_patronal" => $empleadorSantaCruz->numero_patronal,
            ])
            ->create();
        $this->assertDatabaseCount("lista_mora", 2);

        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/quitar", [
                "empleador_id" => $empleadorLaPaz->id
            ]);

        $response->assertOk();
        $this->assertDatabaseMissing("lista_mora", [
            "empleador_id" => $empleadorLaPaz->id
        ]);
        $this->assertDatabaseCount("lista_mora", 1);
        
        
        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/quitar", [
                "empleador_id" => $empleadorSantaCruz->id
            ]);

        $response->assertOk();
        $this->assertDatabaseMissing("lista_mora", [
            "empleador_id" => $empleadorSantaCruz->id
        ]);
        $this->assertDatabaseCount("lista_mora", 0);
    }
    
    public function test_usuario_con_permiso_regional_para_agregar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::AGREGAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL
            ])
            ->create();

        $empleadorLaPaz = Empleador::factory()
            ->regionalLaPaz()
            ->create();
            
        $empleadorSantaCruz = Empleador::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/agregar", [
                "empleador_id" => $empleadorLaPaz->id
            ]);

        $response->assertOk();
        $this->assertDatabaseHas("lista_mora", [
            "empleador_id" => $empleadorLaPaz->id
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/agregar", [
                "empleador_id" => $empleadorSantaCruz->id
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing("lista_mora", [
            "empleador_id" => $empleadorSantaCruz->id
        ]);
    }
    
    public function test_usuario_con_permiso_regional_para_quitar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::QUITAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL
            ])
            ->create();
        
        $empleadorLaPaz = Empleador::factory()
            ->regionalLaPaz()
            ->create();
        ListaMoraItem::factory()
            ->regionalLaPaz()
            ->for($empleadorLaPaz)
            ->state([
                "nombre" => $empleadorLaPaz->nombre,
                "numero_patronal" => $empleadorLaPaz->numero_patronal,
            ])
            ->create();
        $empleadorSantaCruz = Empleador::factory()
        ->regionalSantaCruz()
        ->create();
        ListaMoraItem::factory()
            ->regionalSantaCruz()
            ->for($empleadorSantaCruz)
            ->state([
                "nombre" => $empleadorSantaCruz->nombre,
                "numero_patronal" => $empleadorSantaCruz->numero_patronal,
            ])
            ->create();
        $this->assertDatabaseCount("lista_mora", 2);

        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/quitar", [
                "empleador_id" => $empleadorLaPaz->id
            ]);

        $response->assertOk();
        $this->assertDatabaseMissing("lista_mora", [
            "empleador_id" => $empleadorLaPaz->id
        ]);
        $this->assertDatabaseCount("lista_mora", 1);
        
        
        $response = $this->actingAs($user)
            ->postJson("/api/lista-mora/quitar", [
                "empleador_id" => $empleadorSantaCruz->id
            ]);

        $response->assertForbidden();
        $this->assertDatabaseHas("lista_mora", [
            "empleador_id" => $empleadorSantaCruz->id
        ]);
    }
}
