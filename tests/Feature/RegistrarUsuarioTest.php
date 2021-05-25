<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrarUsuario extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_nombre_de_usuario_repetido()
    {   
        $username = "usuario";
        User::factory()->state([
            "username" => $username
        ])->create();

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', [
                "username" => $username
            ]);

        $response->assertJsonValidationErrors([
            "username" => "El valor para nombre de usuario ya ha sido tomado."
        ]);
    }
    
    public function test_ci_repetido()
    {   
        $ci = "12345678";
        $conflictUser = User::factory()->state([
            "ci_raiz" => $ci,
            "ci_complemento" => null
        ])->create()->refresh();

        $user = $this->getSuperUser();

        $roles = Role::factory()->count(1)->create();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', [
                "ci" => $ci,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "username" => "usuario",
                "password" => "contraseña",
                "regional_id" => 1,
                "roles" => $roles->map(fn ($r) => $r->name)
            ]);
        $response->assertStatus(409);
        $response->assertJsonFragment([
            "payload" => $conflictUser->toArray()
        ]);
    }
}
