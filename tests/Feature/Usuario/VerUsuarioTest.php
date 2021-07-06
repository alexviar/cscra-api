<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VerUsuarioTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_ver()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::VER_USUARIOS
            ])
            ->create();

        $usuario = User::factory()
                ->create();
        $usuario->refresh();
        $response = $this->actingAs($loggedUser)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertOk();
        $response->assertJson([
            "id" => $usuario->id,
            "ci_raiz" => $usuario->ci_raiz,
            "ci_complemento" => $usuario->ci_complemento,
            "ci" => $usuario->ci,
            "apellido_paterno" => $usuario->apellido_paterno,
            "apellido_materno" => $usuario->apellido_materno,
            "nombres" => $usuario->nombres,
            "nombre_completo" => $usuario->nombre_completo,
            "username" => $usuario->username,
            "estado" => $usuario->estado,
            "estado_text" => $usuario->estadoText,
            "roles" => $usuario->roles->toArray(),
            "all_permissions" => $usuario->getAllPermissions()->toArray(),
            "created_at" => $usuario->created_at->format("Y-m-d"),
            "updated_at" => $usuario->created_at->format("Y-m-d")
        ]);
    }

    public function test_usuario_puede_ver_regionalmente()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::VER_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO
            ])
            ->create();

        $usuario = User::factory()
            ->create();
        $usuario->refresh();
        $response = $this->actingAs($loggedUser)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());

        $usuario = User::factory()
            ->regionalSantaCruz()
            ->create();
        $response = $this->actingAs($loggedUser)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $loggedUser = User::factory()
            ->create();
        
        $usuario = User::factory()
            ->create();
        $response = $this->actingAs($loggedUser)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertForbidden();
    }
}
