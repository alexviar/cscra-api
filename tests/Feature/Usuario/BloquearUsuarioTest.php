<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BloquearUsuarioTest extends TestCase
{
    private function assertLocked(TestResponse $response, $usuario){
        $response->assertOk();
        $this->assertTrue($usuario->estado == 1);
        $usuario->refresh();
        $this->assertTrue($usuario->estado == 2);
    }
    
    private function assertUnlocked(TestResponse $response, $usuario){
        $response->assertOk();
        $this->assertTrue($usuario->estado == 2);
        $usuario->refresh();
        $this->assertTrue($usuario->estado == 1);
    }

    public function test_usuario_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)->putJson("/api/usuarios/100/bloquear");
        $response->assertNotFound();

        $response = $this->actingAs($user)->putJson("/api/usuarios/100/desbloquear");
        $response->assertNotFound();
    }

    public function test_usuario_puede_bloquear()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::BLOQUEAR_USUARIOS
            ])
            ->create();

        //Misma regional
        $usuario =  User::factory()->regionalLaPaz()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $this->assertLocked($response, $usuario);

        //Distinta regional
        $usuario =  User::factory()->regionalSantaCruz()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $this->assertLocked($response, $usuario);
    }

    public function test_usuario_puede_desbloquear()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::DESBLOQUEAR_USUARIOS
            ])
            ->create();

        //Misma regional
        $usuario =  User::factory()->bloqueado()->regionalLaPaz()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $this->assertUnlocked($response, $usuario);

        //Distinta regional
        $usuario =  User::factory()->bloqueado()->regionalSantaCruz()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $this->assertUnlocked($response, $usuario);
    }

    public function test_usuario_puede_bloquear_regionalmente()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::BLOQUEAR_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        //Misma regional
        $usuario =  User::factory()->regionalLaPaz()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $this->assertLocked($response, $usuario);

        //Distinta regional
        $usuario =  User::factory()->regionalSantaCruz()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $response->assertForbidden();

        //El permiso regional es prioritario
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::BLOQUEAR_USUARIOS,
                Permisos::BLOQUEAR_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        $this->assertTrue($login->hasPermissionTo(Permisos::BLOQUEAR_USUARIOS));

        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $response->assertForbidden();
    }

    public function test_usuario_puede_desbloquear_regionalmente()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::DESBLOQUEAR_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        //Misma regional
        $usuario =  User::factory()->bloqueado()->regionalLaPaz()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $this->assertUnlocked($response, $usuario);

        //Distinta regional
        $usuario =  User::factory()->bloqueado()->regionalSantaCruz()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $response->assertForbidden();

        //El permiso regional es prioritario
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::BLOQUEAR_USUARIOS,
                Permisos::BLOQUEAR_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        $this->assertTrue($login->hasPermissionTo(Permisos::BLOQUEAR_USUARIOS));

        $response = $this->actingAs($login)
            ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        /** @var User $login */
        $login = User::factory()
            ->create();

        $usuario =  User::factory()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $response->assertForbidden();
        

        $usuario =  User::factory()->bloqueado()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        /** @var User $login */
        $login = User::factory()
            ->superUser()
            ->create();

        $usuario =  User::factory()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $this->assertLocked($response, $usuario);
        
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $this->assertUnlocked($response, $usuario);
    }

    public function test_usuario_bloqueado()
    {
        /** @var User $login */
        $login = User::factory()
            ->withPermissions([
                Permisos::BLOQUEAR_USUARIOS,
                Permisos::DESBLOQUEAR_USUARIOS
            ])
            ->bloqueado()
            ->create();

        $usuario =  User::factory()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/bloquear");
        $response->assertForbidden();
        

        $usuario =  User::factory()->bloqueado()->create();
        $response = $this->actingAs($login)
                ->putJson("/api/usuarios/{$usuario->id}/desbloquear");
        $response->assertForbidden();
    }

    public function test_usuario_no_authenticado()
    {
        $response = $this->putJson("/api/usuarios/100/bloquear");
        $response->assertUnauthorized();
        
        $response = $this->putJson("/api/usuarios/100/desbloquear");
        $response->assertUnauthorized();
    }
}
