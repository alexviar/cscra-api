<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CambiarContrasenaTest extends TestCase
{

    private function assertPasswordChanged(TestResponse $response, $user, $password){
        $response->assertOk();
        $this->assertTrue($user->validatePassword($password));
    }

    public function test_usuario_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)->putJson("/api/usuarios/100/cambiar-contrasena", []);
        $response->assertNotFound();
    }

    public function test_password()
    {
        $user = $this->getSuperUser();

        $usuario = User::factory()->create();

        $data = [
            "password" => "asQWrt#$"
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $response->assertJsonValidationErrors([
            "password" => "La contraseña debe contener al menos un número"
        ]);

        $data["password"] = "abcdefG1";
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $response->assertJsonValidationErrors([
            "password" => "La contraseña debe contener al menos un símbolo"
        ]);

        $data["password"] = "1234567(";
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $response->assertJsonValidationErrors([
            "password" => "La contraseña debe contener al menos una letra mayuscula y una letra minuscula"
        ]);
        
        $data["password"] = "aB2345(";
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $response->assertJsonValidationErrors([
            "password" => "Este campo debe contener al menos 8 caracteres"
        ]);
        
        $data["password"] = "aB23456(";
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $this->assertPasswordChanged($response, $usuario->fresh(), $data["password"]);
    }

    public function test_usuario_puede_cambiar_contrasena()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::CAMBIAR_CONTRASEÑAS
            ])
            ->create();

        //A un usuario de la misma regional
        $usuario = User::factory()
        ->regionalLaPaz()
        ->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $this->assertPasswordChanged($response, $usuario->fresh(), $data["password"]);
        
        //A un usuario de distinta regional
        $usuario = User::factory()
        ->regionalSantaCruz()
        ->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $this->assertPasswordChanged($response, $usuario->fresh(), $data["password"]);
    }

    public function test_usuario_puede_cambiar_contrasena_regionalmente()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::CAMBIAR_CONTRASEÑAS_MISMA_REGIONAL
            ])
            ->create();

        //A un usuario de la misma regional
        $usuario = User::factory()
        ->regionalLaPaz()
        ->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $this->assertPasswordChanged($response, $usuario->fresh(), $data["password"]);
        
        //A un usuario de distinta regional
        $usuario = User::factory()
        ->regionalSantaCruz()
        ->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $response->assertForbidden();

        //El permiso regional tiene mayor prioridad
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::CAMBIAR_CONTRASEÑAS,
                Permisos::CAMBIAR_CONTRASEÑAS_MISMA_REGIONAL
            ])
            ->create();
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $response->assertForbidden();
    }

    public function test_usuario_puede_cambiar_su_contrasena()
    {
        /** @var User $login */
        $login = User::factory()->state(["password" => "password"])->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$login->id}/cambiar-contrasena", $data);

        // $response->assertJsonValidationErrors([
        //     "old_password" => "Este campo es requerido"
        // ]);
        $response->assertForbidden();

        $data["old_password"] = "asdfñlkj";
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$login->id}/cambiar-contrasena", $data);
        $response->assertJsonValidationErrors([
            "old_password" => "La contraseña es incorrecta"
        ]);
            
        $data["old_password"] = "password";
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$login->id}/cambiar-contrasena", $data);
        $this->assertPasswordChanged($response, $login->fresh(), $data["password"]);
    }

    public function test_usuario_sin_permisos()
    {
        /** @var User $login */
        $login = User::factory()->create();

        $user = User::factory()->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$user->id}/cambiar-contrasena", $data);
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        /** @var User $login */
        $login = User::factory()->superUser()->create();

        $user = User::factory()->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$user->id}/cambiar-contrasena", $data);
        $this->assertPasswordChanged($response, $user->fresh(), $data["password"]);
    }

    public function test_usuario_bloqueado()
    {
        /** @var User $login */
        $login = User::factory()->create();

        $user = User::factory()->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$user->id}/cambiar-contrasena", $data);
        $response->assertForbidden();
    }

    public function test_usuario_no_authenticado()
    {
        $response = $this->putJson("/api/usuarios/100/cambiar-contrasena", []);
        $response->assertUnauthorized();
    }
}
