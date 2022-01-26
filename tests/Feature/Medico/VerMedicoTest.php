<?php

namespace Tests\Feature\Medico;

use App\Models\Medico;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VerMedicoTest extends TestCase
{

    public function test_usuario_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)->getJson("/api/medicos/2");
        $response->assertNotFound();
    }

    public function test_usuario_puede_ver()
    {
        $user = User::factory()
            ->withPermissions([Permisos::VER_MEDICOS])
            ->regionalLaPaz()
            ->activo()
            ->create();

        $usuario = Medico::factory()
            ->regionalLaPaz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/medicos/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());

        $usuario = Medico::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/medicos/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());
    }

    public function test_usuario_puede_ver_regionalmente()
    {
        $user = User::factory()
            ->regionalLaPaz()
            ->activo()
            ->withPermissions([
                Permisos::VER_MEDICOS_REGIONAL
            ])
            ->create();

        $usuario = Medico::factory()
            ->regionalLaPaz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/medicos/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());

        $usuario = Medico::factory()
            ->regionalSantaCruz()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/medicos/{$usuario->id}");
        $response->assertForbidden();

        $user = User::factory()
            ->regionalLaPaz()
            ->activo()
            ->withPermissions([
                Permisos::VER_MEDICOS,
                Permisos::VER_MEDICOS_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($user)->getJson("/api/medicos/{$usuario->id}");
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $usuario = Medico::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/medicos/{$usuario->id}");
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $user = User::factory()
            ->superUser()
            ->create();

        $usuario = Medico::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/medicos/{$usuario->id}");
        $response->assertOk();
        $response->assertJson($usuario->toArray());
    }

    public function test_usuario_bloqueado()
    {
        $user = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::VER_MEDICOS])
            ->create();

        $usuario = Medico::factory()
            ->create();

        $response = $this->actingAs($user)->getJson("/api/medicos/{$usuario->id}");
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->getJson("/api/medicos/100");
        $response->assertUnauthorized();
    }
}
