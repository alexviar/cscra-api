<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrarRolTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_nombre_repetido()
    {
        $nombre = "rol";
        Role::factory()->state([
            "name" => $nombre
        ])->create();

        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/roles', [
                "name" => $nombre
            ]);

        $response->assertJsonValidationErrors([
            "name" => "Ya existe un rol con el mismo nombre."
        ]);
    }
}
