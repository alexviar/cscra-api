<?php

namespace Tests\Feature\Rol;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EditarRolTest extends TestCase
{
    use WithFaker;
    
    private function assertSuccess(TestResponse $response, $model, $data)
    {
        $response->assertOk();
        $this->assertDatabaseHas($model, [
            "id" => $model->id,
            "name" => $data["name"],
            "description" => $data["description"],
            "guard_name" => "sanctum"
        ]);
        $freshModel = $model->fresh();
        $this->assertTrue($freshModel->hasAllPermissions($data["permissions"]));
        $response->assertJson($freshModel->toArray());
    }

    public function test_not_found()
    {
        $login = $this->getSuperUser();

        $response = $this->actingAs($login)->putJson("/api/roles/100");
        $response->assertNotFound();
    }
    
    public function test_nombre_repetido()
    {
        $user = $this->getSuperUser();

        $rol = Role::factory()->create();
        $existingRol = Role::factory()->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $rol->name
            ]);
        $response->assertJsonMissingValidationErrors(["name"]);

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $existingRol->name
            ]);        
        $response->assertJsonValidationErrors([
            "name" => "Ya existe un rol con el mismo nombre."
        ]);
    }

    public function test_nombre_demasiado_largo()
    {
        $login = $this->getSuperUser();

        $rol = Role::factory()->create();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $this->faker->lexify(str_repeat('?', 50))
            ]);
        $response->assertJsonMissingValidationErrors(["name"]);

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "name" => $this->faker->lexify(str_repeat('?', 51))
            ]);
        $response->assertJsonValidationErrors([
            "name" => "Este campo no debe exceder los 50 caracteres"
        ]);
    }

    public function test_descripcion_demasiado_larga()
    {
        $user = $this->getSuperUser();

        $rol = Role::factory()->create();

        $maxLength = 255;
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "description" => $this->faker->lexify(str_repeat('?', $maxLength)),
            ]);
        $response->assertJsonMissingValidationErrors(["description"]);

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "description" => $this->faker->lexify(str_repeat('?', $maxLength + 1)),
            ]);
        $response->assertJsonValidationErrors([
            "description" => "Este campo no debe exceder los $maxLength caracteres"
        ]);
    }

    public function test_sin_permisos()
    {
        $login = $this->getSuperUser();

        $rol = Role::factory()->create();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/roles/{$rol->id}", [
                "permissions" => []
            ]);
        $response->assertJsonValidationErrors([
            "permissions" => "Debe indicar al menos un permiso"
        ]);
    }

    public function test_campos_requeridos()
    {
        $user = $this->getSuperUser();
        $rol = Role::factory()->create();
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/roles/{$rol->id}", []);
        
        $response->assertJsonValidationErrors([
            "name" => "Este campo es requerido",
            "permissions" => "Debe indicar al menos un permiso"
        ]);
    }

    public function test_usuario_con_permisos()
    {
        $login = User::factory()
            ->withPermissions([
                Permisos::ACTUALIZAR_ROLES
            ])
            ->create();
        
        $rol = Role::factory()->create();

        $allPermisos = Permisos::toArray();
        $permisos = $this->faker->randomElements($allPermisos, $this->faker->numberBetween(1, count($allPermisos)));

        $data = Role::factory()->raw() + [
            "permissions" => $permisos
        ];

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/roles/{$rol->id}", $data);        
        $this->assertSuccess($response, $rol, $data);
    }
    
    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();

        $rol = Role::factory()->create();

        $allPermisos = Permisos::toArray();
        $permisos = $this->faker->randomElements($allPermisos, $this->faker->numberBetween(1, count($allPermisos)));

        $data = Role::factory()->raw() + [
            "permissions" => $permisos
        ];

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/roles/{$rol->id}", $data);
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $rol = Role::factory()->create();

        $allPermisos = Permisos::toArray();
        $permisos = $this->faker->randomElements($allPermisos, $this->faker->numberBetween(1, count($allPermisos)));

        $data = Role::factory()->raw() + [
            "permissions" => $permisos
        ];

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/roles/{$rol->id}", $data);
        $this->assertSuccess($response, $rol, $data);
    }


    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::REGISTRAR_USUARIOS])
            ->create();

        $rol = Role::factory()->create();

        $allPermisos = Permisos::toArray();
        $permisos = $this->faker->randomElements($allPermisos, $this->faker->numberBetween(1, count($allPermisos)));

        $data = Role::factory()->raw() + [
            "permissions" => $permisos
        ];

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/roles/{$rol->id}", $data);
        $response->assertForbidden();
    }


    public function test_usuario_no_autenticado()
    {
        $response = $this->putJson("/api/usuarios/100", []);
        $response->assertUnauthorized();
    }
}
