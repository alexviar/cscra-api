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
    
    public function test_rol_no_existe()
    {  
        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', [
                "ci" => 12345678,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "username" => "usuario",
                "password" => "contraseña",
                "regional_id" => 1,
                "roles" => ["fake rol"]
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            "roles.0" => "El rol seleccionado es invalido"
        ]);
    }
    
    public function test_usuario_sin_apellidos()
    {
        $roles = Role::factory()->count(1)->create();

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', [
                "ci" => 12345678,
                "nombres" => "Nombres",
                "username" => "usuario",
                "password" => "contraseña",
                "regional_id" => 1,
                "roles" => $roles->map(fn ($rol) => $rol->name)
            ]);
        $response->assertJsonValidationErrors([
            "apellido_paterno" => "Debe indicar al menos un apellido",
            "apellido_materno" => "Debe indicar al menos un apellido"
        ]);
    }
}
