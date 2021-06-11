<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RegistrarUsuarioTest extends TestCase
{
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_nombre_de_usuario_repetido()
    {   
        $username = "usuario";
        User::factory()->state([
            "username" => $username
        ])->create();

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', [
                "username" => $username
            ]);

        $response->assertJsonValidationErrors([
            "username" => "El valor para nombre de usuario ya ha sido tomado."
        ]);
    }

    public function test_nombre_de_usuario_demasiado_largo()
    {
        $user = $this->getSuperUser();

        $roles = Role::factory()->count(1)->create();

        $ci = $this->faker->unique()->numerify("########");
        $data = [
            "ci" => $ci,
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $this->faker->lexify(str_repeat('?', 33)),
            "password" => $this->faker->password(8)."1aA!",
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "username" => "Este campo no debe exceder los 32 caracteres"
        ]);

        $data["username"] = $this->faker->lexify(str_repeat('?', 32));
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertOk();
    }

    
    public function test_nombre_de_usuario_demasiado_corto()
    {
        $user = $this->getSuperUser();

        $roles = Role::factory()->count(1)->create();

        $ci = $this->faker->unique()->numerify("########");
        $data = [
            "ci" => $ci,
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $this->faker->lexify(str_repeat('?', 5)),
            "password" => $this->faker->password(8)."1aA!",
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertJsonValidationErrors([
            "username" => "Este campo debe contener al menos 6 caracteres"
        ]);

        $data["username"] = $this->faker->lexify(str_repeat('?', 6));
        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', $data);
        $response->assertOk();
    }

    public function test_password()
    {        
        $user = $this->getSuperUser();

        $roles = Role::factory()->count(1)->create();

        $ci = $this->faker->unique()->numerify("########");
        $data = [
            "ci" => $ci,
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $this->faker->userName,
            "password" => "abcdefG%",
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];

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
        $response->assertOk();
    }
    
    public function test_ci_repetido()
    {
        $loggedUser = $this->getSuperUser();

        $ci=$this->faker->unique()->numerify("########-A1");
        $ci = explode("-",$ci);
        $conflictUser1 = User::factory()->state([
            "ci_raiz" => $ci[0],
            "ci_complemento" => null
        ])->create();
    
        $roles = Role::factory()->count(1)->create();

        $data = [
            "ci" => $ci[0],
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $this->faker->unique()->lexify(str_repeat('?', mt_rand(6, 32))),
            "password" => '$2yY1234',
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        $response->assertStatus(409);
        $response->assertJsonFragment([
            "payload" => $conflictUser1->id
        ]);
        
        $data["ci_complemento"] = $ci[1];
        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        $response->assertOk();

        $newId = json_decode($response->getContent())->id;
        
        $data["username"] = $this->faker->unique()->lexify(str_repeat('?', mt_rand(6, 32)));
        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        $response->assertStatus(409);
        $response->assertJsonFragment([
            "payload" => $newId
        ]);

        $data["ci_complemento"] = "A2";
        $response = $this->actingAs($loggedUser, "sanctum")
            ->postJson("/api/usuarios", $data);
        Log::info($response->getContent());
        $response->assertOk();
    }
    
    public function test_rol_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', [
                "ci" => 12345678,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "username" => "usuario",
                "password" => "contraseña",
                "regional_id" => 1,
                "roles" => ["fake rol"]
            ]);
            
        $response->assertJsonValidationErrors([
            "roles.0" => "El rol seleccionado es invalido"
        ]);
    }
    
    
    public function test_regional_no_existe()
    {  
        $roles = Role::factory()->count(1)->create();

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', [
                "ci" => 12345678,
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "username" => "usuario",
                "password" => "contraseña",
                "regional_id" => 0,
                "roles" => $roles->map(function ($rol) {
                    return $rol->nombre;
                })
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            "regional_id" => "La regional seleccionada es invalida."
        ]);
    }

    public function test_campos_requeridos(){

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/usuarios', []);
        $response->assertJsonValidationErrors([
            "ci" => "Este campo es requerido.",
            "username" => "Este campo es requerido.",
            "password" => "Este campo es requerido.",
            "apellido_paterno" => "Debe indicar al menos un apellido",
            "apellido_materno" => "Debe indicar al menos un apellido",
            "nombres" => "Este campo es requerido.",
            "regional_id" => "Debe indicar una regional.",
            "roles" => "Este campo es requerido.",
        ]);
    }

    public function test_usuario_con_permiso_para_registrar()
    {        
        $roles = Role::factory()->count(1)->create();

        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_USUARIOS
            ])
            ->create();
        
        $data = [
            "ci" => $this->faker->numerify("########"),
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $this->faker->unique()->lexify(str_repeat('?', mt_rand(6, 32))),
            "password" => '$2yY1234',
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        
        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $response->assertOk();
        $this->assertDatabaseHas("users", [
            "ci_raiz" => $data["ci"],
            "ci_complemento" => null,
            "apellido_paterno" => $data["apellido_paterno"],
            "apellido_materno" => $data["apellido_materno"],
            "nombres" => $data["nombres"],
            "username" => $data["username"],
            "regional_id" => $data["regional_id"]
        ]);
        $content = json_decode($response->getContent());
        $user = User::where("id", $content->id)->first();
        $this->assertTrue($user->hasAllRoles($roles->map(function ($rol) {
            return  $rol->name;
        })));
        $this->assertTrue(Hash::check($data["password"], $user->password));
    }
    

    public function test_usuario_puede_registrar_regionalmente()
    {        
        $roles = Role::factory()->count(1)->create();

        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO
            ])
            ->create();

        $data = [
            "ci" => $this->faker->numerify("########"),
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $this->faker->unique()->lexify(str_repeat('?', mt_rand(6, 32))),
            "password" => '$2yY1234',
            "regional_id" => 3,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        
        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $response->assertForbidden();

        $data["regional_id"] = 1;
        $response = $this->actingAs($user)
        ->postJson("/api/usuarios", $data);
        $response->assertOk();
        $this->assertDatabaseHas("users", [
            "ci_raiz" => $data["ci"],
            "ci_complemento" => null,
            "apellido_paterno" => $data["apellido_paterno"],
            "apellido_materno" => $data["apellido_materno"],
            "nombres" => $data["nombres"],
            "username" => $data["username"],
            "regional_id" => $data["regional_id"]
        ]);
        $content = json_decode($response->getContent());
        $user = User::where("id", $content->id)->first();
        $this->assertTrue($user->hasAllRoles($roles->map(function ($rol) {
            return  $rol->name;
        })));
        $this->assertTrue(Hash::check($data["password"], $user->password));
    }

    public function test_usuario_sin_permisos(){
        $roles = Role::factory()->count(1)->create();

        $user = User::factory()
            ->withPermissions([])
            ->create();
        
        $data = [
            "ci" => $this->faker->numerify("########"),
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $this->faker->unique()->lexify(str_repeat('?', mt_rand(6, 32))),
            "password" => '$2yY1234',
            "regional_id" => 3,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        
        $response = $this->actingAs($user)
            ->postJson("/api/usuarios", $data);
        $response->assertForbidden();
    }
    

    public function test_usuario_no_autenticado(){
        $roles = Role::factory()->count(1)->create();
        
        $response = $this->postJson("/api/usuarios", [
                "ci" => 12345678,
                "ci_complemento" => "A1",
                "apellido_paterno" => "Paterno",
                "apellido_materno" => "Materno",
                "nombres" => "Nombres",
                "username" => "usuario",
                "password" => "contraseña",
                "regional_id" => 1,
                "roles" => $roles->map(function ($rol) {
                    return $rol->name;
                })
            ]);
        $response->assertUnauthorized();
    }
}
