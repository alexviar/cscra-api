<?php

namespace Tests\Feature\Prestacion;

use App\Models\Permisos;
use App\Models\Prestacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuscarPrestacionesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_buscar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::VER_PRESTACIONES
            ])
            ->create();

        Prestacion::factory()
            ->count(10)    
            ->create();
        $expected = Prestacion::get()->map(function($prestacion){
            return [
                "id" => $prestacion->id,
                "nombre"=> $prestacion->nombre
            ];
        })->all();
        
        $response = $this->actingAs($user)->getJson('/api/prestaciones');
        $response->assertStatus(200);
        $response->assertJson($expected);
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        prestacion::factory()
            ->count(10)    
            ->create();
        
        $response = $this->actingAs($user)->getJson('/api/prestaciones');
        $response->assertForbidden();
    }

    public function test_sql_injection()
    {
        $user = $this->getSuperUser();

        prestacion::factory()
            ->count(10)    
            ->create();

        $this->actingAs($user)
            ->getJson("/api/prestaciones?".http_build_query([
                "filter" => [
                    "nombre" => "prestacion'; DELETE FROM prestaciones;--"
                ]
            ]));

        $this->assertDatabaseCount("prestaciones_salud", 10);
    }
}
