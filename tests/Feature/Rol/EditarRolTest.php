<?php

namespace Tests\Feature\Rol;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EditarRolTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_nombre_repetido()
    {
        $user = $this->getSuperUser();

        $nombre = "rol";
        $rol = Role::factory()->state([
            "name" => $nombre
        ])->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $nombre,
                "permissions" => [
                    Permisos::AGREGAR_EMPLEADOR_EN_MORA
                ]
            ]);
        $response->assertOk();

        Role::factory()->state([
            "name" => "No disponible"
        ])->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => "No disponible",
                "permissions" => [
                    Permisos::AGREGAR_EMPLEADOR_EN_MORA
                ]
            ]);
        
        $response->assertJsonValidationErrors([
            "name" => "Ya existe un rol con el mismo nombre."
        ]);
    }

    public function test_nombre_demasiado_largo()
    {
        $user = $this->getSuperUser();

        $nombre = "rol";
        $rol = Role::factory()->state([
            "name" => $nombre
        ])->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $this->faker->lexify(str_repeat('?', 50)),
                "permissions" => [
                    Permisos::AGREGAR_EMPLEADOR_EN_MORA
                ]
            ]);
        $response->assertOk();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $this->faker->lexify(str_repeat('?', 51)),
                "permissions" => [
                    Permisos::AGREGAR_EMPLEADOR_EN_MORA
                ]
            ]);
        $response->assertJsonValidationErrors([
            "name" => "Este campo no debe exceder los 50 caracteres"
        ]);
    }

    public function test_descripcion_demasiado_larga()
    {
        $user = $this->getSuperUser();

        $nombre = "rol";
        $rol = Role::factory()->state([
            "name" => $nombre
        ])->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $nombre,
                "description" => $this->faker->lexify(str_repeat('?', 250)),
                "permissions" => [
                    Permisos::AGREGAR_EMPLEADOR_EN_MORA
                ]
            ]);
        $response->assertOk();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $nombre,
                "description" => $this->faker->lexify(str_repeat('?', 251)),
                "permissions" => [
                    Permisos::AGREGAR_EMPLEADOR_EN_MORA
                ]
            ]);
        $response->assertJsonValidationErrors([
            "description" => "Este campo no debe exceder los 250 caracteres"
        ]);
    }

    public function test_sin_permisos()
    {
        $user = $this->getSuperUser();
        $nombre = "rol";
        $rol = Role::factory()->state([
            "name" => $nombre
        ])->create();
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
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
        $nombre = "rol";
        $rol = Role::factory()->state([
            "name" => $nombre
        ])->create();
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", []);
        
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
