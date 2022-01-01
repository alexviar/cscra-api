<?php

namespace Tests\Feature\Rol;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuscarRolTest extends TestCase
{

    public function test_usuario_puede_buscar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::VER_ROLES
            ])
            ->create();

        $response = $this->actingAs($user)
            ->getJson("/api/roles");

        $response->assertStatus(200);
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $response = $this->actingAs($user)
            ->getJson("/api/roles");

        $response->assertForbidden();
    }
}
