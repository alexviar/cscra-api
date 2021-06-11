<?php

namespace Tests\Feature;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
            ->withPermissions([
                Permisos::VER_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO
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
}
