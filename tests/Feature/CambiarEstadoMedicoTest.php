<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CambiarEstadoMedicoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_campos_requeridos()
    {
        $user = $this->getSuperUser();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", []);

        //dd($response->getContent());
        $response->assertJsonValidationErrors([
            "estado" => "El estado es requerido"
        ]);
    }

    public function test_estado_no_valido()
    {
        $user = $this->getSuperUser();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 3
            ]);

        //dd($response->getContent());
        $response->assertJsonValidationErrors([
            "estado" => "El estado es invalido"
        ]);
    }

    public function test_usuario_puede_dar_baja()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::BAJA_MEDICOS
            ])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 2
            ]);

        $response->assertOk();
        $this->assertDatabaseHas("medicos", [
            "id" => $medico->id,
            "estado" => 2
        ]);
    }

    public function test_usuario_puede_dar_alta()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::BAJA_MEDICOS
            ])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->baja()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 1
            ]);

        $response->assertOk();
        $this->assertDatabaseHas("medicos", [
            "id" => $medico->id,
            "estado" => 1
        ]);
    }



    public function test_usuario_puede_dar_baja_solo_en_la_regional_a_la_que_pertenece()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::BAJA_MEDICOS_REGIONAL
            ])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 2
            ]);

        $response->assertOk();
        $this->assertDatabaseHas("medicos", [
            "id" => $medico->id,
            "estado" => 2
        ]);


        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 2
            ]);

        $response->assertForbidden();
    }

    public function test_usuario_puede_dar_alta_solo_en_la_regional_a_la_que_pertenece()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::BAJA_MEDICOS_REGIONAL
            ])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->baja()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 1
            ]);

        $response->assertOk();
        $this->assertDatabaseHas("medicos", [
            "id" => $medico->id,
            "estado" => 1
        ]);

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->baja()
            ->regionalSantaCruz()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 1
            ]);

        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->create();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->baja()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user)
            ->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 1
            ]);

        $response->assertForbidden();
    }

    public function test_usuario_invitado()
    {

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->baja()
            ->for($especialidad)
            ->create();

        $response = $this->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
                "estado" => 1
            ]);

        $response->assertUnauthorized();
    }

    public function test_medico_no_existe(){
        $user = $this->getSuperUser();
        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->baja()
            ->for($especialidad)
            ->create();
        $response = $this->actingAs($user)->putJson("/api/medicos/{$medico->id}/cambiar-estado", [
            "estado" => 1
        ]);
        $response->assertNotFound();
    }
    
}
