<?php

namespace Tests\Feature\Proveedor;

use App\Models\Proveedor;
use App\Models\Permisos;
use App\Models\User;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RegistrarProveedorTest extends TestCase
{
    use WithFaker;

    private function assertSuccess(TestResponse $response, $data)
    {
        $response->assertOk();
        if ($data["tipo"] == 1) {
            $this->assertDatabaseHas("proveedores", [
                "tipo" => 1,
                "estado" => 1,
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
                "estado" => 1,
                "nit" => $data["nit"],
                "nombre" => $data["nombre"],
                "regional_id" => $data["regional_id"],
                "direccion" => $data["direccion"],
                "ubicacion" => DB::raw("point(" . Arr::get($data, "ubicacion.longitud") . "," . Arr::get($data, "ubicacion.latitud") . ")"),
                "telefono1" => $data["telefono1"],
                "telefono2" => $data["telefono2"]
            ]);
        }
        $proveedor = Proveedor::find($response->json("id"));
        // if($proveedor == null) dd($response->json("id"), $proveedor);
        $response->assertJson($proveedor->toArray());
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

    public function test_campos_requeridos()
    {
        $login = $this->getSuperUser();

        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", [
                "tipo" => 1
            ]);

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

        $response = $this->actingAs($login)->postJson("/api/proveedores", [
            "tipo" => 2
        ]);

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
            ->postJson("/api/proveedores", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = Proveedor::factory()->medico()->state([
            "ci" => $existingProveedor2->ci
        ])->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        // Carnet repetido con diferente complemento
        $data = Proveedor::factory()->medico()->state([
            "ci" => (new CarnetIdentidad(12345678, "1A"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        $data = Proveedor::factory()->medico()->state([
            "ci" => (new CarnetIdentidad(2345678, "1B"))
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonMissingValidationErrors(["ci"]);

        //Carnet repetido
        $data = Proveedor::factory()->medico()->state([
            "ci" => $existingProveedor1->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un proveedor registrado con este carnet de identidad."]);

        $data = Proveedor::factory()->medico()->state([
            "ci" => $existingProveedor2->ci
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonValidationErrors(["ci" => "Ya existe un proveedor registrado con este carnet de identidad."]);
    }

    public function test_nit_repetido()
    {
        $login = $this->getSuperUser();

        $existingProveedor1 = Proveedor::factory()->medico()->state([
            "nit" => "123456789012"
        ])->regionalLaPaz()->create();
        $existingProveedor2 = Proveedor::factory()->empresa()->state([
            "nit" => "234567890123"
        ])->regionalLaPaz()->create();

        // Carnet repetido, pero en otra regional
        $data = Proveedor::factory()->empresa()->state([
            "nit" => $existingProveedor1->nit
        ])->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonMissingValidationErrors(["nit"]);

        $data = Proveedor::factory()->empresa()->state([
            "nit" => $existingProveedor2->nit
        ])->regionalSantaCruz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonMissingValidationErrors(["nit"]);

        // Carnet repetido
        $data = Proveedor::factory()->empresa()->state([
            "nit" => $existingProveedor1->nit
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonValidationErrors(["nit" => "Ya existe un proveedor registrado con este NIT."]);

        $data = Proveedor::factory()->empresa()->state([
            "nit" => $existingProveedor2->nit
        ])->regionalLaPaz()->raw();

        $response = $this->actingAs($login, "sanctum")
            ->postJson("/api/proveedores", $data);
        $response->assertJsonValidationErrors(["nit" => "Ya existe un proveedor registrado con este NIT."]);        
    }
        
    public function test_regional_no_existe()
    {
        $login = $this->getSuperUser();

        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/proveedores', [
                "tipo" => 1,
                "regional_id" => 0
            ]);
        $response->assertJsonValidationErrors([
            "regional_id" => "Regional inválida.",
        ]);

        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/proveedores', [
                "tipo" => 2,
                "regional_id" => 0
            ]);
        $response->assertJsonValidationErrors([
            "regional_id" => "Regional inválida.",
        ]);
    }

    public function test_usuario_puede_registrar()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::REGISTRAR_PROVEEDORES,
            ])
            ->create();

        //Misma regional
        //Especialista
        $data = Proveedor::factory()->regionalLaPaz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $this->assertSuccess($response, $data);

        //Empresa
        $data = Proveedor::factory()->regionalLaPaz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $this->assertSuccess($response, $data);

        //Distinta regional
        //Especialista
        $data = Proveedor::factory()->regionalSantaCruz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $this->assertSuccess($response, $data);

        //Empresa
        $data = Proveedor::factory()->regionalSantaCruz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $this->assertSuccess($response, $data);
    }

    public function test_usuario_puede_registrar_regionalmente()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::REGISTRAR_PROVEEDORES_REGIONAL,
            ])
            ->create();


        //Misma regional
        //Especialista
        $data = Proveedor::factory()->regionalLaPaz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $this->assertSuccess($response, $data);

        //Empresa
        $data = Proveedor::factory()->regionalLaPaz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $this->assertSuccess($response, $data);

        //Distinta regional
        //Especialista
        $data = Proveedor::factory()->regionalSantaCruz()->medico()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $response->assertForbidden();

        //Empresa
        $data = Proveedor::factory()->regionalSantaCruz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
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
            ->postJson("/api/proveedores", $data);
        $response->assertForbidden();

        //Empresa
        $data = Proveedor::factory()->regionalSantaCruz()->empresa()->raw();
        $data = $this->prepareData($data);
        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $response->assertForbidden();
    }
    
    public function test_usuario_sin_permisos()
    {
        $login = User::factory()
            ->withPermissions([])
            ->create();
        
        $data = Proveedor::factory()->empresa()->raw();
        $data = $this->prepareData($data);
        
        $response = $this->actingAs($login, "sanctum")
            ->postJson('/api/proveedores', $data);
        $response->assertForbidden();
    }    

    public function test_super_usuario()
    {
        $login = User::factory()->superUser()->create();

        $data = Proveedor::factory()->empresa()->raw();
        $data = $this->prepareData($data);

        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $this->assertSuccess($response, $data);
    }

    public function test_usuario_bloqueado()
    {
        $login = User::factory()
            ->bloqueado()
            ->withPermissions([Permisos::REGISTRAR_PROVEEDORES])
            ->create();

        $data = Proveedor::factory()->empresa()->raw();
        $data = $this->prepareData($data);

        $response = $this->actingAs($login)
            ->postJson("/api/proveedores", $data);
        $response->assertForbidden();
    }

    public function test_usuario_no_autenticado()
    {
        $response = $this->postJson('/api/proveedores', []);
        $response->assertUnauthorized();
    }
}
