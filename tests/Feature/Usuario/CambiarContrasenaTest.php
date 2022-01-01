<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CambiarContrasenaTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_cambiar_contrasena()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::CAMBIAR_CONTRASEÑA
            ])
            ->create();
        $usuario = User::factory()->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($loggedUser)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $response->assertOk();
        $usuario->refresh();
        $this->assertTrue($usuario->validatePassword($data["password"]));
    }

    public function test_usuario_puede_cambiar_contrasena_regionalmente()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::CAMBIAR_CONTRASEÑA_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO
            ])
            ->create();
        $usuario = User::factory()->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($loggedUser)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        Log::info($response->getContent());
        $response->assertOk();
        $usuario->refresh();
        $this->assertTrue($usuario->validatePassword($data["password"]));

        
        $usuario = User::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($loggedUser)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);

        $response->assertForbidden();
    }

    public function test_usuario_puede_cambiar_su_contrasena()
    {
        $usuario = User::factory()->create();

        $data = [
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($usuario)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);

        $response->assertJsonValidationErrors([
            "old_password" => "Este campo es requerido"
        ]);

        $data["old_password"] = "asdfñlkj";
        $response = $this->actingAs($usuario)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);
        $response->assertJsonValidationErrors([
            "old_password" => "La contraseña es incorrecta"
        ]);
            
        $data["old_password"] = "password";
        $response = $this->actingAs($usuario)
            ->putJson("/api/usuarios/{$usuario->id}/cambiar-contrasena", $data);

        $response->assertOk();
        $usuario->refresh();
        $this->assertTrue($usuario->validatePassword($data["password"]));
    }

    public function test_usuario_sin_permisos()
    {
        $loggedUser = User::factory()->create();

        $user = User::factory()->create();


        $data = [
            "old_password" => "password",
            "password" => "asQW12#$"
        ];
        $response = $this->actingAs($loggedUser)
            ->putJson("/api/usuarios/{$user->id}/cambiar-contrasena", $data);
        $response->assertForbidden();

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
        $response->assertOk();
    }
}
