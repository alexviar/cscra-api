<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RegistrarUsuarioTest extends TestCase
{
    use WithFaker;

    private function assertInsert(TestResponse $response, $data)
    {
        $response->assertOk();
        $this->assertDatabaseHas("users", [
            "ci" => $data["ci"]->raiz,
            "ci_complemento" => $data["ci"]->complemento,
            "apellido_paterno" => $data["apellido_paterno"],
            "apellido_materno" => $data["apellido_materno"],
            "nombre" => $data["nombre"],
            "username" => $data["username"],
            "estado" => 1,
            "regional_id" => $data["regional_id"]
        ]);
        $user = User::where("ci", $data["ci"]->raiz)->first();
        $response->assertJsonFragment($user->toArray());
        $this->assertTrue($user->hasAllRoles($data["roles"]));
        $this->assertTrue($user->validatePassword($data["password"]));
    }

    public function test_nombre_de_usuario_repetido()
    {
        $existingUser = User::factory()->create();

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', [
                "username" => $existingUser->username
            ]);

        $response->assertJsonValidationErrors([
            "username" => "El valor para nombre de usuario ya ha sido tomado."
        ]);
    }

    public function test_longitud_del_nombre_de_usuario()
    {
        $user = $this->getSuperUser();


        $data = User::factory()->state([
            "username" => $this->faker->lexify(str_repeat('?', 33))
        ])->raw();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "username" => "Este campo no debe exceder los 32 caracteres"
        ]);

        $data["username"] = $this->faker->lexify(str_repeat('?', 32));
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonMissingValidationErrors(["username"]);

        $data["username"] = $this->faker->lexify(str_repeat('?', 6));
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonMissingValidationErrors(["username"]);

        $data["username"] = $this->faker->lexify(str_repeat('?', 5));
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "username" => "Este campo debe contener al menos 6 caracteres"
        ]);
    }

    public function test_password()
    {
        $user = $this->getSuperUser();

        $ci = $this->faker->unique()->numerify("########");
        $data = User::factory()->state([
            "password" => "abcdefG%"
        ])->raw();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "password" => "La contraseña debe contener al menos un número"
        ]);

        $data["password"] = "abcdefG1";
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "password" => "La contraseña debe contener al menos un símbolo"
        ]);

        $data["password"] = "1234567(";
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "password" => "La contraseña debe contener al menos una letra mayuscula y una letra minuscula"
        ]);

        $data["password"] = "aB2345(";
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "password" => "Este campo debe contener al menos 8 caracteres"
        ]);

        $data["password"] = "aB23456(";
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonMissingValidationErrors(["password"]);
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

        //No hay conflicto
        $data = User::factory()->state([
            "ci" => (new CarnetIdentidad(12345678, ""))
        ])->regionalSantaCruz()->raw();

        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = User::factory()->state([
            "ci" => (new CarnetIdentidad(12345678, "1A"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = User::factory()->state([
            "ci" => (new CarnetIdentidad(2345678, "1B"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        //Hay conflicto
        $data = User::factory()->state([
            "ci" => $existingUser1->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un usuario registrado con este carnet de identidad."]);

        $data = User::factory()->state([
            "ci" => $existingUser2->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un usuario registrado con este carnet de identidad."]);
    }

    public function test_rol_no_existe()
    {
        $user = $this->getSuperUser();

        $data = User::factory()->raw();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data + ["roles" => ["fake rol"]]);

        $response->assertJsonValidationErrors([
            "roles.0" => "El rol seleccionado es invalido"
        ]);
    }


    public function test_regional_no_existe()
    {
        $user = $this->getSuperUser();

        $data = User::factory()->state(["regional_id" => -1])->raw();


        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "regional_id" => "La regional seleccionada es invalida."
        ]);
    }

    public function test_campos_requeridos()
    {

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', []);
        $response->assertJsonValidationErrors([
            "ci.raiz" => "Este campo es requerido.",
            "username" => "Este campo es requerido.",
            "password" => "Este campo es requerido.",
            "apellido_paterno" => "Debe indicar al menos un apellido",
            "apellido_materno" => "Debe indicar al menos un apellido",
            "nombre" => "Este campo es requerido.",
            "regional_id" => "Debe indicar una regional.",
            "roles" => "Este campo es requerido.",
        ]);
    }

    public function test_usuario_con_permiso_para_registrar()
    {
        $roles = Role::factory()->count(1)->create();

        $user = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::REGISTRAR_USUARIOS
            ])
            ->create();

        $data = User::factory()
            ->state([
                "username" => "cosme.fulanito",
                "password" => "<aQ123Ed>"
            ])->regionalLaPaz()->raw() + [
                "roles" => $roles->map(function ($r) {
                    return $r->name;
                })
            ];

        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $this->assertInsert($response, $data);

        $data = User::factory()
            ->state([
                "username" => "cosme.fulanito2",
                "password" => "<aQ123Ed>"
            ])->regionalSantaCruz()->raw() + [
                "roles" => $roles->map(function ($r) {
                    return $r->name;
                })
            ];

        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $this->assertInsert($response, $data);
    }

    public function test_usuario_con_permiso_para_registrar_solo_en_su_regional()
    {
        $roles = Role::factory()->count(1)->create();

        $user = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::REGISTRAR_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        $data = User::factory()
            ->state([
                "username" => "cosme.fulanito",
                "password" => "<aQ123Ed>"
            ])->regionalLaPaz()->raw() + [
                "roles" => $roles->map(function ($r) {
                    return $r->name;
                })
            ];

        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $this->assertInsert($response, $data);

        $data = User::factory()
            ->state([
                "username" => "cosme.fulanito2",
                "password" => "<aQ123Ed>"
            ])->regionalSantaCruz()->raw() + [
                "roles" => $roles->map(function ($r) {
                    return $r->name;
                })
            ];

        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $response->assertForbidden();

        $user = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::REGISTRAR_USUARIOS,
                Permisos::REGISTRAR_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $roles = Role::factory()->count(1)->create();

        $data = User::factory()
            ->state([
                "username" => "cosme.fulanito",
                "password" => "<aQ123Ed>"
            ])->raw() + [
                "roles" => Arr::pluck($roles, "name")
            ];

        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $user = $this->getSuperUser();

        $roles = Role::factory()->count(1)->create();

        $data = User::factory()
            ->state([
                "username" => "cosme.fulanito",
                "password" => "<aQ123Ed>"
            ])->raw() + [
                "roles" => Arr::pluck($roles, "name")
            ];

        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $this->assertInsert($response, $data);
    }


    public function test_usuario_bloqueado()
    {
        $user = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::REGISTRAR_USUARIOS])
            ->create();

        $roles = Role::factory()->count(1)->create();

        $data = User::factory()
            ->state([
                "username" => "cosme.fulanito",
                "password" => "<aQ123Ed>"
            ])->raw() + [
                "roles" => Arr::pluck($roles, "name")
            ];

        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->postJson("/api/usuarios", []);
        $response->assertUnauthorized();
    }
}
