<?php

namespace Tests\Feature\Usuario;

use App\Models\ValueObjects\CarnetIdentidad;
use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EditarUsuarioTest extends TestCase
{
    use WithFaker;

    private function assertUpdate(TestResponse $response, $model, $data)
    {
        $response->assertOk();
        $this->assertDatabaseHas("users", [
            "id" => $model->id,
            "username" => $model->username,
            "password" => $model->password,
            "ci" => $data["ci"]->raiz,
            "ci_complemento" => $data["ci"]->complemento,
            "apellido_paterno" => $data["apellido_paterno"],
            "apellido_materno" => $data["apellido_materno"],
            "nombre" => $data["nombre"],
            "estado" => 1,
            "regional_id" => $data["regional_id"]
        ]);
        $user = $model->fresh();
        $this->assertTrue($user->hasAllRoles($data["roles"]));
        $response->assertJsonFragment($user->toArray());
    }
    
    public function test_usuario_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)->putJson("/api/usuarios/100", []);
        $response->assertNotFound();
    }

    public function test_ci_repetido()
    {
        $loggedUser = $this->getSuperUser();

        $existingUser1 = User::factory()->state([
            "ci" => new CarnetIdentidad(12345678, "")
        ])->regionalLaPaz()->create();
        $existingUser2 = User::factory()->state([
            "ci" => new CarnetIdentidad(2345678, "1A")
        ])->regionalLaPaz()->create();
        $existingUser3 = User::factory()->state([
            "ci" => new CarnetIdentidad(12345678, "")
        ])->regionalSantaCruz()->create();

        $data = User::factory()->state([
            "ci" => $existingUser1->ci
        ])->regionalLaPaz()->raw();
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$existingUser1->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = User::factory()->state([
            "ci" => $existingUser1->ci
        ])->regionalSantaCruz()->raw();
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$existingUser1->id}", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un usuario registrado con este carnet de identidad."]);

        $data = User::factory()->state([
            "ci" => $existingUser2->ci
        ])->regionalLaPaz()->raw();
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$existingUser1->id}", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un usuario registrado con este carnet de identidad."]);

        $data = User::factory()->state([
            "ci" => $existingUser2->ci
        ])->regionalSantaCruz()->raw();
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$existingUser1->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);
    }

    public function test_rol_no_existe()
    {
        $loggedUser = $this->getSuperUser();

        $user = User::factory()
            ->create();

        $data = User::factory()->raw() + ["roles" => ["Invalid role"]];
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$user->id}", $data);

        $response->assertJsonValidationErrors([
            "roles.0" => "El rol seleccionado es invalido"
        ]);
    }

    public function test_regional_no_existe()
    {
        $loggedUser = $this->getSuperUser();

        $user = User::factory()
            ->create();

        $data = User::factory()->state(["regional_id" => 0])->raw();
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$user->id}", $data);

        $response->assertJsonValidationErrors([
            "regional_id" => "La regional seleccionada es invalida."
        ]);
    }

    public function test_campos_requeridos()
    {

        $loggedUser = $this->getSuperUser();

        $user = User::factory()->create();

        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$user->id}", []);
        $response->assertJsonValidationErrors([
            "ci.raiz" => "Este campo es requerido.",
            "apellido_paterno" => "Debe indicar al menos un apellido",
            "apellido_materno" => "Debe indicar al menos un apellido",
            "nombre" => "Este campo es requerido.",
            "regional_id" => "Debe indicar una regional.",
            "roles" => "Este campo es requerido.",
        ]);
    }

    public function test_usuario_puede_editar()
    {
        $user = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_USUARIOS
            ])
            ->create();

        $userLaPaz = User::Factory()
            ->regionalLaPaz()
            ->create();
        $userSantaCruz = User::Factory()
            ->regionalSantaCruz()
            ->create();

        $roles = Role::factory()->count(1)->create();


        $data = User::factory()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userLaPaz->id}", $data);

        $this->assertUpdate($response, $userLaPaz, $data);


        $data = User::factory()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userSantaCruz->id}", $data);

        $this->assertUpdate($response, $userSantaCruz, $data);
    }


    public function test_usuario_puede_editar_regionalmente()
    {
        $user = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        $userLaPaz = User::Factory()
            ->regionalLaPaz()
            ->create();
        $userSantaCruz = User::Factory()
            ->regionalSantaCruz()
            ->create();

        $roles = Role::factory()->count(1)->create();


        $data = User::factory()->regionalLaPaz()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userLaPaz->id}", $data);

        $this->assertUpdate($response, $userLaPaz, $data);

        
        $data = User::factory()->regionalSantaCruz()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userLaPaz->id}", $data);

        $response->assertForbidden();

        
        $data = User::factory()->regionalSantaCruz()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userSantaCruz->id}", $data);

        $response->assertForbidden();


        $data = User::factory()->regionalLaPaz()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userSantaCruz->id}", $data);

        $response->assertForbidden();


        $user = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_USUARIOS,
                Permisos::ACTUALIZAR_USUARIOS_MISMA_REGIONAL,
            ])
            ->create();
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userSantaCruz->id}", $data);
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $userLaPaz = User::Factory()
            ->regionalLaPaz()
            ->create();

        $roles = Role::factory()->count(1)->create();

        $data = User::factory()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userLaPaz->id}", $data);
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $user = $this->getSuperUser();

        $userLaPaz = User::Factory()
            ->regionalLaPaz()
            ->create();

        $roles = Role::factory()->count(1)->create();

        $data = User::factory()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userLaPaz->id}", $data);
        $this->assertUpdate($response, $userLaPaz, $data);
    }

    public function test_usuario_bloqueado()
    {
        $user = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::ACTUALIZAR_USUARIOS])
            ->create();

        $userLaPaz = User::Factory()
            ->regionalLaPaz()
            ->create();

        $roles = Role::factory()->count(1)->create();

        $data = User::factory()->raw() + [
            "roles" => Arr::pluck($roles, "name")
        ];
        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/usuarios/{$userLaPaz->id}", $data);
        $response->assertForbidden();
    }


    public function test_usuario_no_autenticado()
    {
        $response = $this->putJson("/api/usuarios/100", []);
        $response->assertUnauthorized();
    }
}
