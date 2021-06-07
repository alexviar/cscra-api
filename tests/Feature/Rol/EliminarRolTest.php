<?php

namespace Tests\Feature\Rol;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EliminarRolTest extends TestCase
{
    use WithFaker;

    public function test_usuario_puede_eliminar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::ELIMINAR_ROLES
            ])
            ->create();

        $rol = Role::factory()->create();
        $rol->syncPermissions([
            Permisos::AGREGAR_EMPLEADOR_EN_MORA,
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA
        ]);
        User::factory()
            ->count(10)
            ->hasAttached($rol)
            ->create();
        $permissionsCount = Permission::count();

        $response = $this->actingAs($user)
            ->deleteJson("/api/roles/{$rol->id}");

        $response->assertOk();
        $this->assertDatabaseMissing("role_has_permissions", [
            "role_id" => $rol->id
        ]);
        $this->assertDatabaseMissing("model_has_roles", [
            "role_id" => $rol->id
        ]);
        $this->assertDatabaseCount("users", 12);
        $this->assertDatabaseCount("permissions", $permissionsCount);
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $rol = Role::factory()->create();
        $rol->syncPermissions([
            Permisos::AGREGAR_EMPLEADOR_EN_MORA,
            Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA
        ]);
        User::factory()
            ->count(10)
            ->hasAttached($rol)
            ->create();

        $response = $this->actingAs($user)
            ->deleteJson("/api/roles/{$rol->id}");
        $response->assertForbidden();
        $this->assertDatabaseHas("roles", ["id" => $rol->id]);
    }

    
}
