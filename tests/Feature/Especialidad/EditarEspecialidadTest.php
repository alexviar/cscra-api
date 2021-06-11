<?php

namespace Tests\Feature\Especialidad;

use App\Models\Especialidad;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EditarEspecialidadTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_editar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::EDITAR_ESPECIALIDADES
            ])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $data = [
            "nombre" => $this->faker->unique()->text(50)
        ];
        $response = $this->actingAs($user)
            ->putJson("/api/especialidades/{$especialidad->id}", $data);

        $response->assertStatus(200);
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->create();
        
        $especialidad = Especialidad::factory()->create();
        $data = [
            "nombre" => $this->faker->unique()->text(50)
        ];
        $response = $this->actingAs($user)
            ->putJson("/api/especialidades/{$especialidad->id}", $data);
        $response->assertForbidden();
    }

    public function test_campos_requeridos()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_ESPECIALIDADES
            ])
            ->create();

        $especialidad = Especialidad::factory()->create();
        $data = [];
        $response = $this->actingAs($user)
            ->putJson("/api/especialidades/{$especialidad->id}", $data);

        $response->assertJsonValidationErrors([
            "nombre" => "Este campo es requerido"
        ]);
    }
}
