<?php

namespace Tests\Feature\Medico;

use App\Models\Medico;
use App\Models\Permisos;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CambiarEstadoMedicoTest extends TestCase
{

    private function assertSuccess(TestResponse $response, $model, $data)
    {
        $response->assertOk();
        $this->assertDatabaseHas("medicos", [
            "id" => $model->id,
            "ci" => $model->ci->raiz,
            "ci_complemento" => $model->ci->complemento,
            "apellido_paterno" => $model->apellido_paterno,
            "apellido_materno" => $model->apellido_materno,
            "nombre" => $model->nombre,
            "especialidad" => $model->especialidad,
            "estado" => $data["estado"],
            "regional_id" => $model->regional_id
        ]);
    }

    public function test_medico_no_existe(){
        $login = $this->getSuperUser();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/medicos/0/actualizar-estado", []);
        $response->assertNotFound();
    }

    public function test_campos_requeridos()
    {
        $login = $this->getSuperUser();

        $medico = Medico::factory()
            ->create();

        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", []);

        //dd($response->getContent());
        $response->assertJsonValidationErrors([
            "estado" => "El estado es requerido"
        ]);
    }

    public function test_estado_no_valido()
    {
        $login = $this->getSuperUser();

        $medico = Medico::factory()
            ->create();

        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", [
                "estado" => 3
            ]);

        $response->assertJsonValidationErrors([
            "estado" => "El estado es invalido"
        ]);
    }

    public function test_usuario_puede_cambiar_estado()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_ESTADO_MEDICOS
            ])
            ->create();

        //Misma regional
        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();

        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
        $this->assertSuccess($response, $medico, $data);

        $data = [
            "estado" => 1
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
        $this->assertSuccess($response, $medico, $data);

        
        //Distinta regional
        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->create();

        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
        $this->assertSuccess($response, $medico, $data);

        $data = [
            "estado" => 1
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
        $this->assertSuccess($response, $medico, $data);
    }

    public function test_usuario_puede_cambiar_estados_dentro_de_su_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_ESTADO_MEDICOS_REGIONAL
            ])
            ->create();

        //Misma regional
        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();

        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
        $this->assertSuccess($response, $medico, $data);

        $data = [
            "estado" => 1
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
        $this->assertSuccess($response, $medico, $data);

        
        //Distinta regional
        $medico = Medico::factory()
            ->regionalSantaCruz()
            ->create();

        $data = [
            "estado" => 2
        ];
        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
        $response->assertForbidden();
    }

    public function test_usuario_sin_permisos()
    {
        /** @var User $login */
        $login = User::factory()
            ->create();

        $medico = Medico::factory()
            ->baja()
            ->create();

        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", [
                "estado" => 1
            ]);
        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $medico = Medico::factory()
            ->create();

        $data = [
            "estado" => 2
        ];

        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
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

        $data = [
            "estado" => 2
        ];

        $response = $this->actingAs($login)
            ->putJson("/api/medicos/{$medico->id}/actualizar-estado", $data);
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->putJson("/api/medicos/0/actualizar-estado", [
                "estado" => 1
            ]);
        $response->assertUnauthorized();
    }    
}
