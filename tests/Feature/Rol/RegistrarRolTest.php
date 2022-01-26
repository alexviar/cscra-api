<?php

namespace Tests\Feature\Rol;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RegistrarRolTest extends TestCase
{
    use WithFaker;
    
    private function assertInsert(TestResponse $response, $data)
    {
        $response->assertOk();
        $this->assertDatabaseHas("roles", [
            "name" => $data["name"],
            "guard_name" => "sanctum"
        ]);
        $createdRole = Role::where("name", $data["name"])->first();
        $this->assertTrue($createdRole->hasAllPermissions($data["permissions"]));
    }

    public function test_nombre_repetido()
    {
        $existingRole = Role::factory()->create();

        $login = $this->getSuperUser();

        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/roles', [
                "name" => $existingRole->name
            ]);

        $response->assertJsonValidationErrors([
            "name" => "Ya existe un rol con el mismo nombre."
        ]);
    }    

    public function test_nombre_demasiado_largo()
    {
        $login = $this->getSuperUser();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", [
                "name" => $this->faker->lexify(str_repeat('?', 50))
            ]);
        $response->assertJsonMissingValidationErrors(["name"]);

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", [
                "name" => $this->faker->lexify(str_repeat('?', 51))
            ]);
        $response->assertJsonValidationErrors([
            "name" => "Este campo no debe exceder los 50 caracteres"
        ]);
    }

    public function test_descripcion_demasiado_larga()
    {
        $login = $this->getSuperUser();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", [
                "name" => $this->faker->text(50),
                "description" => $this->faker->lexify(str_repeat('?', 255))
            ]);
        $response->assertJsonMissingValidationErrors(["description"]);

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", [
                "name" => $this->faker->text(50),
                "description" => $this->faker->lexify(str_repeat('?', 256))
            ]);
        $response->assertJsonValidationErrors([
            "description" => "Este campo no debe exceder los 255 caracteres"
        ]);
    }

    public function test_roles_deben_tener_al_menos_un_permisos()
    {
        $login = $this->getSuperUser();
        
        $response = $this->actingAs($login, "sanctum")
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
        $login = $this->getSuperUser();
        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", []);
        
        $response->assertJsonValidationErrors([
            "name" => "Este campo es requerido",
            "permissions" => "Debe indicar al menos un permiso"
        ]);
    }

    public function test_usuario_con_permisos()
    {
        $login = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_ROLES
            ])
            ->create();

        $allPermisos = Permisos::toArray();
        $permisos = $this->faker->randomElements($allPermisos, $this->faker->numberBetween(1, count($allPermisos)));

        $data = [
            "name" => "Test rol",
            "permissions" => $permisos
        ];
        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", $data);
        
        $this->assertInsert($response, $data);
    }
    
    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();

        $allPermisos = Permisos::toArray();
        $permisos = $this->faker->randomElements($allPermisos, $this->faker->numberBetween(1, count($allPermisos)));

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", [
                "name" => "Test rol",
                "permissions" => $permisos
            ]);
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $allPermisos = Permisos::toArray();
        $permisos = $this->faker->randomElements($allPermisos, $this->faker->numberBetween(1, count($allPermisos)));

        $data = [
            "name" => "Test rol",
            "permissions" => $permisos
        ];

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", $data);
        $this->assertInsert($response, $data);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::REGISTRAR_USUARIOS])
            ->create();

        $allPermisos = Permisos::toArray();
        $permisos = $this->faker->randomElements($allPermisos, $this->faker->numberBetween(1, count($allPermisos)));

        $data = [
            "name" => "Test rol",
            "permissions" => $permisos
        ];

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/roles", $data);
        $response->assertForbidden();
    }


    public function test_usuario_no_autenticado()
    {
        $response = $this->postJson("/api/usuarios", []);
        $response->assertUnauthorized();
    }
}
