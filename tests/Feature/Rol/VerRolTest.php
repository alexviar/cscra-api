<?php

namespace Tests\Feature\Rol;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VerRolTest extends TestCase
{

    public function test_rol_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)->getJson("/api/roles/100");
        $response->assertNotFound();
    }

    public function test_usuario_puede_ver()
    {
        $user = User::factory()
            ->withPermissions([Permisos::VER_ROLES])
            ->create();

        $rol = Role::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/roles/{$rol->id}");
        $response->assertOk();
        $response->assertJson($rol->toArray());
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $rol = Role::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/roles/{$rol->id}");
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $user = User::factory()
            ->superUser()
            ->create();

        $rol = Role::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/roles/{$rol->id}");
        $response->assertOk();
        $response->assertJson($rol->toArray());
    }

    public function test_usuario_bloqueado()
    {
        $user = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::VER_USUARIOS])
            ->create();

        $rol = Role::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/roles/{$rol->id}");
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->getJson("/api/roles/100");
        $response->assertUnauthorized();
    }
}
