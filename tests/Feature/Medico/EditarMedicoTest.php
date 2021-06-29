<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EditarMedicoTest extends TestCase
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

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", []);

        $response->assertJsonValidationErrors([
            "ci" => "Este campo es requerido.",
            "apellido_paterno" => "Debe indicar al menos un apellido",
            "apellido_materno" => "Debe indicar al menos un apellido",
            "nombres" => "Este campo es requerido.",
            "regional_id" => "Debe indicar una regional.",
            "especialidad_id" => "Debe indicar una especialidad.",
        ]);
    }

    public function test_ci_repetido()
    {
        $user = $this->getSuperUser();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", [
                "tipo" => 1,
                "ci" => $medico->ci,
                "ci_complemento" => $medico->ci_complemento,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => 1,
                "especialidad_id" => $especialidad->id
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            "id",
            "ci" => [
                "raiz",
                "complemento"
            ],
            "ci_text",
            "estado",
            "estado_text",
            "apellido_paterno",
            "apellido_materno",
            "nombres",
            "nombre_completo",
            "especialidad"
        ]);
        $this->assertDatabaseHas("medicos", [
            "ci" => $medico->ci,
            "ci_complemento" => $medico->ci_complemento,
            "apellido_paterno" => "Paterno",
            "apellido_materno" => "Materno",
            "nombres" => "Nombres",
            "regional_id" => 1,
            "especialidad_id" => $especialidad->id
        ]);

        $medico1 = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", [
                "tipo" => 1,
                "ci" => $medico1->ci,
                "ci_complemento" => $medico1->ci_complemento,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => 1,
                "especialidad_id" => $especialidad->id
            ]);
        $response->assertStatus(409);
        $response->assertJson([
            "message" => "Existe un registro con el mismo carnet de identidad.",
            "payload" => $medico1->toArray()
        ]);
    }

    public function test_medico_no_existe(){
        $user = $this->getSuperUser();
        $especialidad = Especialidad::factory()->create();
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/1", [
                "tipo" => 1,
                "ci" => 12345678,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => 1,
                "especialidad_id" => $especialidad->id
            ]);
        $response->assertNotFound();
    }

    public function test_especialidad_no_existe()
    {
        $user = $this->getSuperUser();
        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", [
                "tipo" => 1,
                "ci" => 12345678,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => 1,
                "especialidad_id" => -1
            ]);
        $response->assertJsonValidationErrors([
            "especialidad_id" => "La especialidad no es válida.",
        ]);
    }

    
    public function test_regional_no_existe()
    {
        $user = $this->getSuperUser();
        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", [
                "tipo" => 1,
                "ci" => 12345678,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => -1,
                "especialidad_id" => $especialidad->id
            ]);
        $response->assertJsonValidationErrors([
            "regional_id" => "La regional no es válida.",
        ]);
    }
    
    public function test_usuario_puede_editar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::EDITAR_MEDICOS
            ])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", [
                "tipo" => 1,
                "ci" => 12345678,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => 1,
                "especialidad_id" => $especialidad->id
            ]);
        $response->assertOk();
        $response->assertJsonStructure([
            "id",
            "ci" => [
                "raiz",
                "complemento"
            ],
            "ci_text",
            "estado",
            "estado_text",
            "apellido_paterno",
            "apellido_materno",
            "nombres",
            "nombre_completo",
            "especialidad"
        ]);
        $this->assertDatabaseHas("medicos", [
            "ci" => 12345678,
            "apellido_paterno" => "Paterno",
            "apellido_materno" => "Materno",
            "nombres" => "Nombres",
            "regional_id" => 1,
            "especialidad_id" => $especialidad->id
        ]);
    }    
    
    public function test_usuario_puede_registrar_solo_en_su_misma_regional()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::EDITAR_MEDICOS_REGIONAL
            ])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", [
                "tipo" => 1,
                "ci" => 12345678,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => 1,
                "especialidad_id" => $especialidad->id
            ]);
        $response->assertOk();
        $response->assertJsonStructure([
            "id",
            "ci" => [
                "raiz",
                "complemento"
            ],
            "ci_text",
            "estado",
            "estado_text",
            "apellido_paterno",
            "apellido_materno",
            "nombres",
            "nombre_completo",
            "especialidad"
        ]);

        $this->assertDatabaseHas("medicos", [
            "ci" => 12345678,
            "apellido_paterno" => "Paterno",
            "apellido_materno" => "Materno",
            "nombres" => "Nombres",
            "regional_id" => 1,
            "especialidad_id" => $especialidad->id
        ]);
        
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", [
                "tipo" => 1,
                "ci" => 12345679,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => 3,
                "especialidad_id" => $especialidad->id
            ]);
        $response->assertForbidden();
    }
    
    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();
        
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", [
                "tipo" => 1,
                "ci" => 12345679,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "regional_id" => 3,
                "especialidad_id" => $especialidad->id
            ]);
        $response->assertForbidden();
    }

    public function test_guest()
    {
        $especialidad = Especialidad::factory()->create();
        $medico = Medico::factory()
            ->for($especialidad)
            ->create();
        $response = $this->putJson("/api/medicos/{$medico->id}", [
            "tipo" => 1,
            "ci" => 12345679,
            "apellido_paterno" => "Paterno",
            "apellido_materno" => "Materno",
            "nombres" => "Nombres",
            "regional_id" => 3,
            "especialidad_id" => 1
        ]);
        $response->assertUnauthorized();
    }
}
