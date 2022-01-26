<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VerUsuarioTest extends TestCase
{

    public function test_usuario_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)->getJson("/api/usuarios/2");
        $response->assertNotFound();
    }

    public function test_usuario_puede_ver()
    {
        $user = User::factory()
            ->withPermissions([Permisos::VER_USUARIOS])
            ->regionalLaPaz()
            ->activo()
            ->create();

        $usuario = User::factory()
            ->regionalLaPaz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());

        $usuario = User::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());
    }

    public function test_usuario_puede_ver_regionalmente()
    {
        $user = User::factory()
            ->regionalLaPaz()
            ->activo()
            ->withPermissions([
                Permisos::VER_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        $usuario = User::factory()
            ->regionalLaPaz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());

        $usuario = User::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertForbidden();

        $user = User::factory()
            ->regionalLaPaz()
            ->activo()
            ->withPermissions([
                Permisos::VER_USUARIOS,
                Permisos::VER_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $usuario = User::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $user = User::factory()
            ->superUser()
            ->create();

        $usuario = User::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());
    }

    public function test_usuario_bloqueado()
    {
        $user = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::VER_USUARIOS])
            ->create();

        $usuario = User::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios/{$usuario->id}");
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->getJson("/api/usuarios/100");
        $response->assertUnauthorized();
    }
}
