<?php

namespace Tests\Feature;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BloquearUsuarioTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_bloquear()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::BLOQUEAR_USUARIOS
            ])
            ->create();

        $usuario =  User::factory()->create();
        $response = $this->actingAs($loggedUser)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $response->assertOk();
        $this->assertTrue($usuario->refresh()->estado == 2);
    }

    public function test_usuario_puede_desbloquear()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::DESBLOQUEAR_USUARIOS
            ])
            ->create();

        $usuario =  User::factory()->create();
        $response = $this->actingAs($loggedUser)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $response->assertOk();
        $this->assertTrue($usuario->refresh()->estado == 1);
    }

    public function test_usuario_puede_bloquear_regionalmente()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::BLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO
            ])
            ->create();

        $usuario =  User::factory()->create();
        $response = $this->actingAs($loggedUser)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $response->assertOk();
        $this->assertTrue($usuario->refresh()->estado == 2);

        $usuario =  User::factory()->regionalSantaCruz()->create();
        $response = $this->actingAs($loggedUser)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $response->assertForbidden();
    }

    public function test_usuario_puede_desbloquear_regionalmente()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::DESBLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO
            ])
            ->create();

        $usuario =  User::factory()->create();
        $response = $this->actingAs($loggedUser)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $response->assertOk();
        $this->assertTrue($usuario->refresh()->estado == 1);
        

        $usuario =  User::factory()->regionalSantaCruz()->create();
        $response = $this->actingAs($loggedUser)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $loggedUser = User::factory()
            ->create();

        $usuario =  User::factory()->create();
        $response = $this->actingAs($loggedUser)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $response->assertForbidden();
        

        $usuario =  User::factory()->bloqueado()->create();
        $response = $this->actingAs($loggedUser)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $response->assertForbidden();
    }
}
