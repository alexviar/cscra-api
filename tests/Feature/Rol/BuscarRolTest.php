<?php

namespace Tests\Feature\Rol;

use App\Models\Permisos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BuscarRolTest extends TestCase
{

    private function assertSucces(TestResponse $response, $meta, $data)
    {
        $response->assertOk();
        // dd([
        //         "meta" => $meta,
        //         "records" => $data->toArray()
        // ], $response->json());
        $response->assertJson([
            "meta" => $meta,
            "records" => $data->toArray()
        ]);
    }

    public function test_usuario_puede_buscar()
    {
        $login = User::factory()
            ->withPermissions([
                Permisos::VER_ROLES
            ])
            ->create();

        Role::factory()->count(20)->create();

        $response = $this->actingAs($login)
            ->getJson("/api/roles?".http_build_query([
                "page" => [ "current" => 1, "size" => 10]
            ]));
        $this->assertSucces($response, [
            "total" => 22,
            "nextPage" => 2
        ], Role::limit(10)->get());
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $response = $this->actingAs($user)
            ->getJson("/api/roles");
        $response->assertForbidden();
    }

    public function test_filter_by_text()
    {
        $rol = Role::factory()->state([
            "name" => "Administradores de usuarios",
            "description" => null
        ])->withPermissions([
            Permisos::VER_ROLES
        ])->create();
        
        /** @var User $login */
        $login = User::factory()
            ->hasAttached($rol)
            ->create();
        
        $rol2 = Role::factory()->state([
                "name" => "Adminìstrâdöres de otra cosa",
                "description" => "Grupo para los"
            ])->create();
                    
        $rol3 = Role::factory()->state([
            "name" => "Test rol3",
            "description" => "Grupo para los administradores"
        ])->create();
        
        Role::factory()->state([
            "name" => "Test rol4",
            "description" => null
        ])->create();

        DB::commit();

        $response = $this->actingAs($login)
            ->getJson("/api/roles?".http_build_query([
                "page" => [ "current" => 1, "size" => 10],
                "filter" => [ "_busqueda" => "admin"]
            ]));
        $this->assertSucces($response, [
            "total" => 3
        ], collect([$rol, $rol2, $rol3]));

        RefreshDatabaseState::$migrated = false;
        $this->refreshDatabase();
    }
}
