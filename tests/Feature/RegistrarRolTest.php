<?php

namespace Tests\Feature;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RegistrarRolTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_nombre_repetido()
    {
        $nombre = "rol";
        Role::factory()->state([
            "name" => $nombre
        ])->create();

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/roles', [
                "name" => $nombre
            ]);

        $response->assertJsonValidationErrors([
            "name" => "Ya existe un rol con el mismo nombre."
        ]);
    }

    public function test_sin_permisos()
    {
        $user = $this->getSuperUser();
        $response = $this->actingAs($user, "sanctum")
            ->postJson("/api/roles", [
                "name" => "Test rol",
                "permissions" => []
            ]);
        $response->assertJsonValidationErrors([
            "permissions" => "Debe indicar al menos un permiso"
        ]);
    }

    public function test_campos_requeridos()
    {
        $user = $this->getSuperUser();
        $response = $this->actingAs($user, "sanctum")
            ->postJson("/api/roles", []);
        
        $response->assertJsonValidationErrors([
            "name" => "Este campo es requerido",
            "permissions" => "Debe indicar al menos un permiso"
        ]);
    }

    public function test_usuario_con_permiso_para_registrar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_ROLES
            ])
            ->create();

        $permisos = [
            Permisos::REGISTRAR_USUARIOS,
            Permisos::EDITAR_USUARIOS,
            Permisos::BLOQUEAR_USUARIOS,
            Permisos::DESBLOQUEAR_USUARIOS,
            Permisos::CAMBIAR_CONTRASEÑA
        ];

        $response = $this->actingAs($user, "sanctum")
            ->postJson("/api/roles", [
                "name" => "Test rol",
                "permissions" => $permisos
            ]);
        
        $response->assertOk();
        $this->assertDatabaseHas("roles", [
            "name" => "Test rol"
        ]);
        $createdRole = Role::where("name", "Test rol")->first();
        $this->assertTrue($createdRole->hasAllPermissions($permisos));
    }
}
