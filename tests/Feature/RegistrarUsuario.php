<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrarUsuario extends TestCase
{

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_nombre_de_usuario_repetido()
    {        
        $response = $this->postJson('usuarios', [
            
        ]);

        $response->assertStatus(200);
    }
}
