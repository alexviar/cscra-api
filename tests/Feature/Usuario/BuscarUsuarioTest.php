<?php

namespace Tests\Feature\Usuario;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BuscarUsuarioTest extends TestCase
{
    public function test_usuario_puede_buscar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::VER_USUARIOS
            ])
            ->create();

        User::factory()->count(10)->create();
        
        $response = $this->actingAs($user)->getJson('/api/usuarios');
        $response->assertStatus(200);
        $response->assertJson(User::get()->toArray());
    }

    public function test_usuario_puede_buscar_regionalmente()
    {
        $user = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::VER_USUARIOS_MISMA_REGIONAL
            ])
            ->create();

        User::factory()->count(5)->create();
        User::factory()->regionalSantaCruz()->count(5)->create();

        $response = $this->actingAs($user)->getJson("/api/usuarios");
        $response->assertForbidden();
        
        $response = $this->actingAs($user)->getJson("/api/usuarios?filter[regional_id]=3");
        $response->assertForbidden();

        $response = $this->actingAs($user)->getJson("/api/usuarios?filter[regional_id]=1");
        $response->assertOk();
        $response->assertJson(User::where("regional_id", $user->regional_id)->get()->toArray());
    }

    //TODO: Test filters
    public function test_filter_by_nombre_completo()
    {
        $login = $this->getSuperUser();

        $lorena = User::factory()->state([
            "nombre" => "Lorena",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Fulanito"
        ])->create();
        $lorem =  User::factory()->state([
            "nombre" => "Cosme",
            "apellido_materno" => "Lörem",
            "apellido_paterno" => "Fulanito"
        ])->create();
        $lord = User::factory()->state([
            "nombre" => "Cosme",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Lord"
        ])->create();
        User::factory()->state([
            "nombre" => "Cosme",
            "apellido_materno" => "Fulanito",
            "apellido_paterno" => "Fulanito"
        ])->create();

        DB::commit();
        RefreshDatabaseState::$migrated = false;
        $this->beforeApplicationDestroyed(function() use($login){
            $this->refreshDatabase();
            // User::truncate();
            // User::create($login);
        });

        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "nombre_completo" => "lor"
        ];

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 3
            ],
            "records" => collect([$lorena, $lorem, $lord])->toArray()
        ]);
    }

    public function test_filter_by_ci()
    {
        $login = $this->getSuperUser();

        $usuario = User::factory()->create();
        User::factory()->count(10)->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "ci" => $usuario->ci->toArray()
        ];

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 1
            ],
            "records" => collect([$usuario])->toArray()
        ]);
    }

    public function test_filter_by_username()
    {
        $login = $this->getSuperUser();

        $usuario = User::factory()->create();
        User::factory()->count(10)->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "username" => $usuario->username
        ];

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 1
            ],
            "records" => collect([$usuario])->toArray()
        ]);
    }

    public function test_filter_by_estado()
    {
        $login = $this->getSuperUser();

        $usuarioActivo = User::factory()->create();
        $usuarioBloqueado = User::factory()->bloqueado()->create();

        $this->assertDatabaseCount("users", 3);

        $page = [
            "current" => 1,
            "size" => 10
        ];
        $filter = [
            "estado" => 1
        ];

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 2
            ],
            "records" => collect([$login, $usuarioActivo])->toArray()
        ]);
        
        $filter = [
            "estado" => 2
        ];

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page,
            "filter" => $filter
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 1
            ],
            "records" => collect([$usuarioBloqueado])->toArray()
        ]);

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 3
            ],
            "records" => collect([$login, $usuarioActivo, $usuarioBloqueado])->toArray()
        ]);
    }

    public function test_filter_by_regional()
    {
        $login = $this->getSuperUser();

        $usuarioLaPaz = User::factory()->regionalLaPaz()->create();
        $usuarioSantaCruz = User::factory()->regionalSantaCruz()->create();

        $page = [
            "current" => 1,
            "size" => 10
        ];

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page,
            "filter" => [
                "regional_id" => $usuarioLaPaz->regional_id
            ]
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 2
            ],
            "records" => collect([$login, $usuarioLaPaz])->toArray()
        ]);

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page,
            "filter" => [
                "regional_id" => $usuarioSantaCruz->regional_id
            ]
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 1
            ],
            "records" => collect([$usuarioSantaCruz])->toArray()
        ]);

        $response = $this->actingAs($login)->getJson("/api/usuarios?".http_build_query([
            "page" => $page
        ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 3
            ],
            "records" => collect([$login, $usuarioLaPaz, $usuarioSantaCruz])->toArray()
        ]);
    }
}
