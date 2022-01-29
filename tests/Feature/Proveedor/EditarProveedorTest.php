<?php

namespace Tests\Feature\Proveedor;

use App\Models\Especialidad;
use App\Models\Permisos;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EditarProveedorTest extends TestCase
{
    use WithFaker;

    private function assertSuccess(TestResponse $response, $model, $data)
    {
        $response->assertOk();
        if ($model->tipo == 1) {
            $this->assertDatabaseHas("proveedores", [
                "tipo" => 1,
                "estado" => $model->estado,
                "nit" => $data["nit"],
                "ci" => Arr::get($data, "ci.raiz"),
                "ci_complemento" => Arr::get($data, "ci.complemento"),
                "apellido_paterno" => $data["apellido_paterno"],
                "apellido_materno" => $data["apellido_materno"],
                "nombre" => $data["nombre"],
                "especialidad" => $data["especialidad"],
                "regional_id" => $data["regional_id"],
                "direccion" => $data["direccion"],
                "ubicacion" => DB::raw("point(" . Arr::get($data, "ubicacion.longitud") . "," . Arr::get($data, "ubicacion.latitud") . ")"),
                "telefono1" => $data["telefono1"],
                "telefono2" => $data["telefono2"]
            ]);
        } else {
            $this->assertDatabaseHas("proveedores", [
                "tipo" => 2,
                "estado" => $model->estado,
                "nit" => $data["nit"],
                "nombre" => $data["nombre"],
                "regional_id" => $data["regional_id"],
                "direccion" => $data["direccion"],
                "ubicacion" => DB::raw("point(" . Arr::get($data, "ubicacion.longitud") . "," . Arr::get($data, "ubicacion.latitud") . ")"),
                "telefono1" => $data["telefono1"],
                "telefono2" => $data["telefono2"]
            ]);
        }
        $freshModel = $model->fresh();
        $response->assertJson($freshModel->toArray());
    }

    private function prepareData($data)
    {
        if ($data["tipo"] == 1) $data["ci"] = $data["ci"]->toArray();
        $data["ubicacion"] = [
            "latitud" => $data["ubicacion"]->getLat(),
            "longitud" => $data["ubicacion"]->getLng()
        ];
        return $data;
    }

    public function test_proveedor_not_found()
    {
        $login = $this->getSuperUser();
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/0", []);
        $response->assertNotFound();
    }

    public function test_campos_requeridos()
    {
        $login = $this->getSuperUser();

        $proveedor = Proveedor::factory()->medico()->create();

        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}", []);

        $response->assertJsonValidationErrors([
            "nit" => "Este campo es requerido.",
            "ci.raiz" => "Este campo es requerido",
            "apellido_paterno" => "Debe indicar al menos un apellido",
            "apellido_materno" => "Debe indicar al menos un apellido",
            "nombre" => "Este campo es requerido",
            "especialidad" => "Este campo es requerido",
            "regional_id" => "Debe indicar una regional",
            "ubicacion.latitud" => "Este campo es requerido",
            "ubicacion.longitud" => "Este campo es requerido",
            "direccion" => "Este campo es requerido",
            "telefono1" => "Este campo es requerido"
        ]);

        $proveedor = Proveedor::factory()->empresa()->create();

        $response = $this->actingAs($login)->putJson("/api/proveedores/{$proveedor->padded_id}", []);

        $response->assertJsonValidationErrors([
            "nit" => "Este campo es requerido.",
            "nombre" => "Este campo es requerido",
            "regional_id" => "Debe indicar una regional",
            "direccion" => "Este campo es requerido",
            "ubicacion.latitud" => "Este campo es requerido",
            "ubicacion.longitud" => "Este campo es requerido",
            "telefono1" => "Este campo es requerido"
        ]);

        $response->assertJsonMissingValidationErrors(["ci", "ci.raiz", "ci.complemento", "apellido_paterno", "apellido_materno", "especialidad"]);
    }

    public function test_ci_repetido()
    {
        $login = $this->getSuperUser();

        $existingProveedor1 = Proveedor::factory()->medico()->state([
            "ci" => new CarnetIdentidad(12345678, "")
        ])->regionalLaPaz()->create();
        $existingProveedor2 = Proveedor::factory()->medico()->state([
            "ci" => new CarnetIdentidad(2345678, "1A")
        ])->regionalLaPaz()->create();

        // Carnet repetido, pero en otra regional
        $data = Proveedor::factory()->medico()->state([
            "ci" => $existingProveedor1->ci
        ])->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/proveedores/{$existingProveedor2->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        // Mismo proveedor
        $data = Proveedor::factory()->medico()->state([
            "ci" => $existingProveedor2->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/proveedores/{$existingProveedor2->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        // Carnet repetido con diferente complemento
        $data = Proveedor::factory()->medico()->state([
            "ci" => (new CarnetIdentidad(12345678, "1A"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/proveedores/{$existingProveedor2->id}", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        //Carnet repetido
        $data = Proveedor::factory()->medico()->state([
            "ci" => $existingProveedor1->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/proveedores/{$existingProveedor2->id}", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un proveedor registrado con este carnet de identidad."]);
    }
        
    public function test_regional_no_existe()
    {
        $user = $this->getSuperUser();

        $proveedor = Proveedor::factory()->medico()->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/proveedores/{$proveedor->padded_id}", [
                "regional_id" => 0
            ]);
        $response->assertJsonValidationErrors([
            "regional_id" => "Regional inválida.",
        ]);        

        $proveedor = Proveedor::factory()->empresa()->create();

        $response = $this->actingAs($user, "sanctum")
            ->putJson("/api/proveedores/{$proveedor->padded_id}", [
                "regional_id" => 0
            ]);
        $response->assertJsonValidationErrors([
            "regional_id" => "Regional inválida.",
        ]);
    }
    
    public function usuario_puede_editar()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_PROVEEDORES,
            ])
            ->create();

        $proveedorMedico = Proveedor::factory()->medico()->create();
        $proveedorEmpresa = Proveedor::factory()->empresa()->create();

        //Misma regional
        //Especialista
        $data = Proveedor::factory()->regionalLaPaz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorMedico->id}", $data);
        $this->assertSuccess($response, $proveedorMedico, $data);

        //Empresa
        $data = Proveedor::factory()->regionalLaPaz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorEmpresa->id}", $data);
        $this->assertSuccess($response, $proveedorEmpresa, $data);

        //Distinta regional
        //Especialista
        $data = Proveedor::factory()->regionalSantaCruz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorMedico->id}", $data);
        $this->assertSuccess($response, $proveedorMedico, $data);

        //Empresa
        $data = Proveedor::factory()->regionalSantaCruz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorEmpresa->id}", $data);
        $this->assertSuccess($response, $proveedorEmpresa, $data);
    }

    public function test_usuario_puede_editar_regionalmente()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::ACTUALIZAR_PROVEEDORES_REGIONAL,
            ])
            ->create();

        $proveedorMedico = Proveedor::factory()->regionalLaPaz()->medico()->create();
        $proveedorEmpresa = Proveedor::factory()->regionalLaPaz()->empresa()->create();

        //Misma regional
        //Especialista
        $data = Proveedor::factory()->regionalLaPaz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorMedico->id}", $data);
        $this->assertSuccess($response, $proveedorMedico, $data);

        //Empresa
        $data = Proveedor::factory()->regionalLaPaz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorEmpresa->id}", $data);
        $this->assertSuccess($response, $proveedorEmpresa, $data);

        //Distinta regional
        //Especialista
        $data = Proveedor::factory()->regionalSantaCruz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorMedico->id}", $data);
        $response->assertForbidden();

        //Empresa
        $data = Proveedor::factory()->regionalSantaCruz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorEmpresa->id}", $data);
        $response->assertForbidden();
        
        $proveedorMedico = Proveedor::factory()->regionalSantaCruz()->medico()->create();
        $proveedorEmpresa = Proveedor::factory()->regionalSantaCruz()->empresa()->create();

        //Especialista
        $data = Proveedor::factory()->regionalSantaCruz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorMedico->id}", $data);
        $response->assertForbidden();

        //Empresa
        $data = Proveedor::factory()->regionalSantaCruz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorEmpresa->id}", $data);
        $response->assertForbidden();

        //Especialista
        $data = Proveedor::factory()->regionalLaPaz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorMedico->id}", $data);
        $response->assertForbidden();

        //Empresa
        $data = Proveedor::factory()->regionalLaPaz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorEmpresa->id}", $data);
        $response->assertForbidden();

        //Precedencia de los permisos regionales
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::REGISTRAR_PROVEEDORES,
                Permisos::REGISTRAR_PROVEEDORES_REGIONAL,
            ])
            ->create();

        //Especialista
        $data = Proveedor::factory()->regionalSantaCruz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorMedico->id}", $data);
        $response->assertForbidden();

        //Empresa
        $data = Proveedor::factory()->regionalSantaCruz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedorEmpresa->id}", $data);
        $response->assertForbidden();
    }
    
    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();

        $proveedor = Proveedor::factory()->empresa()->create();

        $data = Proveedor::factory()->empresa()->raw();
        $data = $this->prepareData($data);
        
        $response = $this->actingAs($login, "sanctum")
            ->putJson("/api/proveedores/{$proveedor->padded_id}", $data);
        $response->assertForbidden();
    }    

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $proveedor = Proveedor::factory()->empresa()->create();

        $data = Proveedor::factory()->empresa()->raw();
        $data = $this->prepareData($data);

        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}", $data);
        $this->assertSuccess($response, $proveedor, $data);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::ACTUALIZAR_PROVEEDORES])
            ->create();

        $proveedor = Proveedor::factory()->empresa()->create();

        $data = Proveedor::factory()->empresa()->raw();
        $data = $this->prepareData($data);

        $response = $this->actingAs($login)
            ->putJson("/api/proveedores/{$proveedor->padded_id}", $data);
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->putJson('/api/proveedores/0', []);
        $response->assertUnauthorized();
    }
}
