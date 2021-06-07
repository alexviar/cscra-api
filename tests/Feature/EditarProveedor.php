<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Permisos;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class EditarProveedor extends TestCase
{
    use WithFaker;
    
    private $especialidad;

    public function setUp(): void
    {
        parent::setUp();
        $this->especialidad = Especialidad::factory()->create();
    }

    private function setDatosProveedorEmpresa(&$data)
    {
        $data = [
            "nit" => Arr::get($data, "general.nit", $this->faker->numerify("###########")),
            "nombre" => Arr::get($data, "general.nombre", $this->faker->text(25)),
            "regional_id" => Arr::get($data, "general.regional_id", 1)
        ];
    }

    private function setDatosProveedorMedico(&$data)
    {
        $ci = $this->faker->numerify("########");
        $data = [
            "nit" => Arr::get($data, "general.nit", $ci . "016"),
            "ci" => Arr::get($data, "general.ci", $ci),
            "apellido_paterno" => Arr::get($data, "general.apellido_paterno", $this->faker->lastName),
            "apellido_materno" => Arr::get($data, "general.apellido_materno", $this->faker->lastName),
            "nombres" => Arr::get($data, "general.nombres", $this->faker->name),
            "especialidad_id" =>  $this->especialidad->id,
            "regional_id" => Arr::get($data, "general.regional_id", 1)
        ];
    }

    public function usuario_puede_editar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::EDITAR_PROVEEDORES
            ])
            ->create();

        //medico
        $proveedor = Proveedor::factory()
            ->medico()
            ->for($this->especialidad)
            ->create();

        $data = [];
        $this->setDatosProveedorMedico($data);
        $response = $this->putJson("/api/proveedores/{$proveedor->id}", $data);
        $response->assertStatus(200);

        $proveedor = Proveedor::factory()
            ->regionalSantaCruz()
            ->medico()
            ->for($this->especialidad)
            ->create();
        $data = [];
        $this->setDatosProveedorMedico($data);
        $response = $this->putJson("/api/proveedores/{$proveedor->id}", $data);
        $response->assertStatus(200);

        //Empresa
        $proveedor = Proveedor::factory()
            ->empresa()
            ->create();

        $data = [];
        $this->setDatosProveedorMedico($data);
        $response = $this->putJson("/api/proveedores/{$proveedor->id}", $data);
        $response->assertStatus(200);

        $proveedor = Proveedor::factory()
            ->regionalSantaCruz()
            ->empresa()
            ->create();
        $data = [];
        $this->setDatosProveedorMedico($data);
        $response = $this->putJson("/api/proveedores/{$proveedor->id}", $data);
        $response->assertStatus(200);
    }
}
