<?php

namespace Tests\Feature\Rol;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EliminarRolTest extends TestCase
{
    use WithFaker;

    private function assertSuccess(TestResponse $response, $model)
    {
        $response->assertOk();
        $this->assertDeleted($model);
    }

    public function test_not_found()
    {
        $login = $this->getSuperUser();

        $response = $this->actingAs($login)->deleteJson("/api/roles/100");
        $response->assertNotFound();
    }

    public function test_usuario_puede_eliminar()
    {
        $login = User::factory()
            ->withPermissions([
                Permisos::ELIMINAR_ROLES
            ])
            ->create();
    
        //Conflicto: Existen usuarios con el rol que se intenta eliminar
        $rol = Role::factory()->create();
        User::factory()
            ->hasAttached($rol)
            ->create();

        $response = $this->actingAs($login)
            ->deleteJson("/api/roles/{$rol->id}");
        $response->status(409);
        $this->assertDatabaseHas($rol, ["name" => $rol->name]);

        //Success
        $rol = Role::factory()->create();
        $response = $this->actingAs($login)
            ->deleteJson("/api/roles/{$rol->id}");        
        $this->assertSuccess($response, $rol);
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $rol = Role::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson("/api/roles/{$rol->id}");
        $response->assertForbidden();
        $this->assertTrue($rol->exists());
    }

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $rol = Role::factory()->create();
        $response = $this->actingAs($login, "sanctum")
            ->deleteJson("/api/roles/{$rol->id}");
        $this->assertSuccess($response, $rol);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::REGISTRAR_USUARIOS])
            ->create();

        $rol = Role::factory()->create();
        $response = $this->actingAs($login, "sanctum")
            ->deleteJson("/api/roles/{$rol->id}");
        $response->assertForbidden();
    }


    public function test_usuario_no_autenticado()
    {
        $response = $this->deleteJson("/api/roles/100", []);
        $response->assertUnauthorized();
    }

    
}
