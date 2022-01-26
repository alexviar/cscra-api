<?php

namespace Tests\Feature\Medico;

use App\Models\Medico;
use App\Models\Permisos;
use App\Models\User;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RegistrarMedicoTest extends TestCase
{
    use WithFaker;

    private function assertSuccess(TestResponse $response, $data)
    {
        $response->assertOk();
        $this->assertDatabaseHas("medicos", [
            "ci" => $data["ci"]->raiz,
            "ci_complemento" => $data["ci"]->complemento,
            "apellido_paterno" => $data["apellido_paterno"],
            "apellido_materno" => $data["apellido_materno"],
            "nombre" => $data["nombre"],
            "especialidad" => $data["especialidad"],
            "estado" => 1,
            "regional_id" => $data["regional_id"]
        ]);
        $user = Medico::find($response->json()["id"]);
        $response->assertJsonFragment($user->toArray());
    }
    
    public function test_campos_requeridos()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/medicos', []);

        $response->assertJsonValidationErrors([
            "ci.raiz" => "Este campo es requerido.",
            "apellido_paterno" => "Debe indicar al menos un apellido",
            "apellido_materno" => "Debe indicar al menos un apellido",
            "nombre" => "Este campo es requerido.",
            "especialidad" => "Debe indicar una especialidad.",
            "regional_id" => "Debe indicar una regional.",
        ]);
    }
    
    public function test_ci_repetido()
    {
        $login = $this->getSuperUser();

        $existingMedico1 = Medico::factory()->state([
            "ci" => new CarnetIdentidad(12345678, "")
        ])->regionalLaPaz()->create();
        $existingMedico2 = Medico::factory()->state([
            "ci" => new CarnetIdentidad(2345678, "1A")
        ])->regionalLaPaz()->create();

        //No hay conflicto
        $data = Medico::factory()->state([
            "ci" => $existingMedico1->ci
        ])->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/medicos", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = Medico::factory()->state([
            "ci" => (new CarnetIdentidad(12345678, "1A"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/medicos", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = Medico::factory()->state([
            "ci" => (new CarnetIdentidad(2345678, "1B"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/medicos", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        //Hay conflicto
        $data = Medico::factory()->state([
            "ci" => $existingMedico1->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/medicos", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un médico registrado con este carnet de identidad."]);

        $data = Medico::factory()->state([
            "ci" => $existingMedico2->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/medicos", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un médico registrado con este carnet de identidad."]);
    }
    
    public function test_regional_no_existe()
    {
        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->postJson('/api/medicos', [
                "regional_id" => 0
            ]);
        $response->assertJsonValidationErrors([
            "regional_id" => "La regional no es válida.",
        ]);
    }
    
    public function test_usuario_puede_registrar()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::REGISTRAR_MEDICOS
            ])
            ->create();

        //Misma regional
        $data = Medico::factory()->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/medicos', $data);
        $this->assertSuccess($response, $data);        

        //Distinta regional
        $data = Medico::factory()->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/medicos', $data);
        $this->assertSuccess($response, $data);
    }    
    
    public function test_usuario_puede_registrar_solo_en_su_misma_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::REGISTRAR_MEDICOS_REGIONAL
            ])
            ->create();

        //Misma regional
        $data = Medico::factory()->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/medicos', $data);
        $this->assertSuccess($response, $data);        

        //Distinta regional
        $data = Medico::factory()->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/medicos', $data);
        $response->assertForbidden();

        //Prioridad de permisos
        $login = User::factory()
        ->regionalLaPaz()
        ->withPermissions([
            Permisos::REGISTRAR_MEDICOS,
            Permisos::REGISTRAR_MEDICOS_REGIONAL
        ])
        ->create();

        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/medicos', $data);
        $response->assertForbidden();
    }
    
    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();
        
        $data = Medico::factory()->raw();
        
        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/medicos', $data);
        $response->assertForbidden();
    }    

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $data = Medico::factory()->raw();

        $response = $this->actingAs($login)
            ->postJson("/api/medicos", $data);
        $this->assertSuccess($response, $data);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::REGISTRAR_MEDICOS])
            ->create();

        $data = Medico::factory()->raw();

        $response = $this->actingAs($login)
            ->postJson("/api/medicos", $data);
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->postJson('/api/medicos', []);
        $response->assertUnauthorized();
    }
}
