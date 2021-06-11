<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuscarMedicoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_ver_medicos()
    {
        $user = $this->getSuperUser();
        
        $especialidad = Especialidad::factory()->create();
        Medico::factory()->count(10)
            ->for($especialidad)
            ->create();
        $response = $this->actingAs($user)->getJson('/api/medicos');
        $response->assertOk(200);
    }
}
