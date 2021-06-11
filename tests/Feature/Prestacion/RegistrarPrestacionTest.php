<?php

namespace Tests\Feature;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrarPrestacionTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_registrar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_PRESTACIONES
            ])
            ->create();

        $data = [
            "nombre" => $this->faker->unique()->text(50)
        ];
        $response = $this->actingAs($user)
            ->postJson("/api/prestaciones", $data);

        $response->assertStatus(200);
    }

    
    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->create();
        
        $data = [
            "nombre" => $this->faker->unique()->text(50)
        ];
        $response = $this->actingAs($user)
            ->postJson("/api/prestaciones", $data);
        $response->assertForbidden();
    }

    public function test_campos_requeridos()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_ESPECIALIDADES
            ])
            ->create();

        $data = [];
        $response = $this->actingAs($user)
            ->postJson("/api/prestaciones", $data);

        $response->assertJsonValidationErrors([
            "nombre" => "Este campo es requerido"
        ]);
    }
}
