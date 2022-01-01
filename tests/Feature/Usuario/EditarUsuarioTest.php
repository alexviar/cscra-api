<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EditarUsuarioTest extends TestCase
{
    use WithFaker;
   
    public function test_ci_repetido()
    {   
        $loggedUser = $this->getSuperUser();

        $ci = $this->faker->numerify("########");
        $conflictUser1 = User::factory()->state([
            "ci_raiz" => $ci,
            "ci_complemento" => null
        ])->create();
        $ci = explode("-", $this->faker->bothify("########-?#"));
        $conflictUser2 = User::factory()->state([
            "ci_raiz" => $ci[0],
            "ci_complemento" => $ci[1]
        ])->create();

        $user = User::factory()->create();

        $roles = Role::factory()->count(1)->create();

        $data = [
            "ci" => $conflictUser1->ci_raiz,
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "password" => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$user->id}", $data);
        $response->assertStatus(409);
        $response->assertJsonFragment([
            "payload" => $conflictUser1->id
        ]);
        
        $data = [
            "ci" => $conflictUser2->ci_raiz,
            "ci_complemento" => $conflictUser2->ci_complemento,
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "password" => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$user->id}", $data);
        $response->assertStatus(409);
        $response->assertJsonFragment([
            "payload" => $conflictUser2->id
        ]);
    }
    
    public function test_rol_no_existe()
    {
        $loggedUser = $this->getSuperUser();

        $user = User::factory()
            ->create();

        $ci = $this->faker->unique()->numerify("########");
        $data = [
            "ci" => $ci,
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "password" => $this->faker->password(8),
            "regional_id" => 1,
            "roles" => ["invalid role"]
        ];
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$user->id}", $data);
            
        $response->assertJsonValidationErrors([
            "roles.0" => "El rol seleccionado es invalido"
        ]);
    }
    
    
    public function test_regional_no_existe()
    {  
        $roles = Role::factory()->count(1)->create();

        $loggedUser = $this->getSuperUser();

        $user = User::factory()
            ->create();

        $ci = $this->faker->unique()->numerify("########");
        $data = [
            "ci" => $ci,
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "password" => $this->faker->password(8),
            "regional_id" => 0,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$user->id}", $data);
        
        $response->assertStatus(422);
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
            "ci" => "Este campo es requerido.",
            "apellido_paterno" => "Debe indicar al menos un apellido",
            "apellido_materno" => "Debe indicar al menos un apellido",
            "nombres" => "Este campo es requerido.",
            "regional_id" => "Debe indicar una regional.",
            "roles" => "Este campo es requerido.",
        ]);
    }

    public function test_usuario_puede_editar()
    {        
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::EDITAR_USUARIOS
            ])
            ->create();

        $user = User::Factory()->create();
        $roles = Role::factory()->count(1)->create();

        $ci = $this->faker->unique()->numerify("########");
        $data = [
            "ci" => $ci,
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $this->faker->username,
            "password" => "1234",
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        $response = $this->actingAs($loggedUser, "sanctum")
            ->putJson("/api/usuarios/{$user->id}", $data);
        
        $response->assertOk();
        $this->assertDatabaseHas("users", [
            "ci_raiz" => $data["ci"],
            "ci_complemento" => null,
            "apellido_paterno" => $data["apellido_paterno"],
            "apellido_materno" => $data["apellido_materno"],
            "nombres" => $data["nombres"],
            "username" => $user->username,
            "regional_id" => $data["regional_id"]
        ]);
        $user->refresh();
        $this->assertTrue($user->hasAllRoles($roles->map(function ($rol) {
            return  $rol->name;
        })));
        $this->assertTrue(Hash::check("password", $user->password));
    }
    

    public function test_usuario_puede_editar_regionalmente()
    {
        $loggedUser = User::factory()
            ->withPermissions([
                Permisos::EDITAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO
            ])
            ->create();

        $user = User::factory()->create();
        $roles = Role::factory()->count(1)->create();

        $ci = $this->faker->unique()->bothify("########-?#");
        $ci = explode("-", $ci);
        $data = [
            "ci" => $ci[0],
            "ci_complemento" => $ci[1],
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $user->username,
            "password" => "1234",
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        $response = $this->actingAs($loggedUser)
            ->putJson("/api/usuarios/{$user->id}", $data);
        $response->assertOk();
        $this->assertDatabaseHas("users", [
            "ci_raiz" => $data["ci"],
            "ci_complemento" => $data["ci_complemento"],
            "apellido_paterno" => $data["apellido_paterno"],
            "apellido_materno" => $data["apellido_materno"],
            "nombres" => $data["nombres"],
            "username" => $user->username,
            "regional_id" => $data["regional_id"]
        ]);
        $this->assertTrue($user->hasAllRoles($roles->map(function ($rol) {
            return  $rol->name;
        })));
        $this->assertTrue(Hash::check("password", $user->password));

        $user2 = User::factory()
            ->regionalSantaCruz()
            ->create();
        $response = $this->actingAs($loggedUser)
            ->putJson("/api/usuarios/{$user2->id}", $data);
        $response->assertForbidden();

        $data["regional_id"] = 3;
        $response = $this->actingAs($loggedUser)
            ->putJson("/api/usuarios/{$user->id}", $data);
        $response->assertForbidden();
    }
    
    public function test_usuario_sin_permisos(){
        $loggedUser = User::factory()
            ->withPermissions([])
            ->create();

        $user = User::factory()->create();
        $roles = Role::factory()->count(1)->create();
        
        $ci = $this->faker->unique()->bothify("########-?#");
        $ci = explode("-", $ci);
        $data = [
            "ci" => $ci[0],
            "ci_complemento" => $ci[1],
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "username" => $user->username,
            "password" => $this->faker->password(8),
            "regional_id" => 1,
            "roles" => $roles->map(function ($r) {
                return $r->name;
            })
        ];
        $response = $this->actingAs($loggedUser)
            ->putJson("/api/usuarios/{$user->id}", $data);
        $response->assertForbidden();
    }
    

    public function test_usuario_no_autenticado(){
        
        $user = User::factory()->create();
        $roles = Role::factory()->count(1)->create();
        
        $response = $this->putJson("/api/usuarios/{$user->id}", [
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
