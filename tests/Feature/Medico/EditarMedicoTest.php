<?php

namespace Tests\Feature\Medico;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\User;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EditarMedicoTest extends TestCase
{

    private function assertSuccess(TestResponse $response, $model, $data)
    {
        $response->assertOk();
        $this->assertDatabaseHas("medicos", [
            "id" => $model->id,
            "ci" => $data["ci"]->raiz,
            "ci_complemento" => $data["ci"]->complemento,
            "apellido_paterno" => $data["apellido_paterno"],
            "apellido_materno" => $data["apellido_materno"],
            "nombre" => $data["nombre"],
            "especialidad" => $data["especialidad"],
            "estado" => $model->estado,
            "regional_id" => $data["regional_id"]
        ]);
        $freshModel = $model->fresh();
        $response->assertJsonFragment($freshModel->toArray());
    }

    public function test_medico_no_existe(){
        $user = $this->getSuperUser();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/0", []);
        $response->assertNotFound();
    }
    
    public function test_campos_requeridos()
    {
        $user = $this->getSuperUser();

        $medico = Medico::factory()
            ->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", []);

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
            ->putJson("/api/medicos/{$existingMedico1->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = Medico::factory()->state([
            "ci" => (new CarnetIdentidad(12345678, "1A"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$existingMedico1->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = Medico::factory()->state([
            "ci" => (new CarnetIdentidad(2345678, "1B"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$existingMedico1->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = Medico::factory()->state([
            "ci" => $existingMedico1->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$existingMedico1->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        //Hay conflicto
        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$existingMedico2->id}", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un médico registrado con este carnet de identidad."]);

        $data = Medico::factory()->state([
            "ci" => $existingMedico2->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$existingMedico1->id}", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un médico registrado con este carnet de identidad."]);
    }
    
    public function test_regional_no_existe()
    {
        $user = $this->getSuperUser();
        
        $medico = Medico::factory()
            ->create();

        $data = Medico::factory([
            "regional_id" => 0
        ])->raw();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $response->assertJsonValidationErrors([
            "regional_id" => "La regional no es válida.",
        ]);
    }
    
    public function test_usuario_puede_editar()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_MEDICOS
            ])
            ->create();

        //Misma regional a misma regional
        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();

        $data = Medico::factory()->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $this->assertSuccess($response, $medico, $data);        

        //Misma regional a distinta
        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();

        $data = Medico::factory()->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $this->assertSuccess($response, $medico, $data);
        
        //Distinta regional a distinta
        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->create();
        $data = Medico::factory()->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $this->assertSuccess($response, $medico, $data);

        //Distinta regional a misma
        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->create();
        $data = Medico::factory()->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $this->assertSuccess($response, $medico, $data);
    }    
    
    public function test_usuario_puede_editar_solo_en_su_misma_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_MEDICOS_REGIONAL
            ])
            ->create();

        //Misma regional a misma regional
        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();

        $data = Medico::factory()->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $this->assertSuccess($response, $medico, $data);        

        //Misma regional a distinta
        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();

        $data = Medico::factory()->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $response->assertForbidden();
        
        //Distinta regional a distinta
        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->create();
        $data = Medico::factory()->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $response->assertForbidden();

        //Distinta regional a misma
        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->create();
        $data = Medico::factory()->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $response->assertForbidden();

        //Precedencia del permiso regional
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_MEDICOS,
                Permisos::ACTUALIZAR_MEDICOS_REGIONAL
            ])
            ->create();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $response->assertForbidden();
    }  
    
    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();

        $medico = Medico::factory()
            ->create();

        $data = Medico::factory()->raw();
        
        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/{$medico->id}", $data);
        $response->assertForbidden();
    } 

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $medico = Medico::factory()
            ->create();

        $data = Medico::factory()->raw();

        $response = $this->actingAs($login)
        ->putJson("/api/medicos/{$medico->id}", $data);
        $this->assertSuccess($response, $medico, $data);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::REGISTRAR_MEDICOS])
            ->create();

        $medico = Medico::factory()
            ->create();

        $data = Medico::factory()->raw();

        $response = $this->actingAs($login)
        ->putJson("/api/medicos/{$medico->id}", $data);
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->putJson("/api/medicos/1", []);
        $response->assertUnauthorized();
    }
}
