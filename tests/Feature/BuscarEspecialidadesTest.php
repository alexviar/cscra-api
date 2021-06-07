<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuscarEspecialidadesTest extends TestCase
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
                Permisos::VER_ESPECIALIDADES
            ])
            ->create();

        Especialidad::factory()
            ->count(10)    
            ->create();
        $expected = Especialidad::get()->map(function($especialidad){
            return [
                "id" => $especialidad->id,
                "nombre"=> $especialidad->nombre
            ];
        })->all();
        
        $response = $this->actingAs($user)->getJson('/api/especialidades');
        $response->assertStatus(200);
        $response->assertJson($expected);
    }

    public function test_usuario_sin_permisos()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        Especialidad::factory()
            ->count(10)    
            ->create();
        $expected = Especialidad::get()->map(function($especialidad){
            return [
                "id" => $especialidad->id,
                "nombre"=> $especialidad->nombre
            ];
        })->all();
        
        $response = $this->actingAs($user)->getJson('/api/especialidades');
        $response->assertForbidden();
    }

    public function test_sql_injection()
    {
        $user = $this->getSuperUser();

        Especialidad::factory()
            ->count(10)    
            ->create();

        $this->actingAs($user)
            ->getJson("/api/especialidades?".http_build_query([
                "filter" => [
                    "nombre" => "especialidad'; DELETE FROM especialidades;--"
                ]
            ]));

        $this->assertDatabaseCount("especialidades", 10);
    }
}
