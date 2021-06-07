<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Proveedor;
use App\Models\Permisos;
use App\Models\Prestacion;
use App\Models\User;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class RegistrarProveedorTest extends TestCase
{
    use WithFaker;

    private $especialidad;
    private $prestaciones;

    public function setUp(): void
    {
        parent::setUp();
        $this->especialidad = Especialidad::factory()->create();
        $this->prestaciones = Prestacion::factory()->count(10)->create();
    }

    private function setDatosProveedor(&$data)
    {
        if(Arr::has($data, "contacto")){
            Arr::set($data, "contacto", [
                "municipio_id" => Arr::get($data, "contacto.municipio_id", 1),
                "direccion" => Arr::get($data, "contacto.direccion", $this->faker->address),
                "ubicacion" => Arr::get($data, "contacto.ubicacion", [
                    "latitud" => $this->faker->latitude,
                    "longitud" => $this->faker->longitude
                ]),
                "telefono1" => Arr::get($data, "contacto.telefono1", 70000000)
            ]);
        }
        
        Arr::set($data, "contrato", [
            "inicio" => Arr::get($data, "contrato.inicio", $this->faker->date()),
            "prestacion_ids" => Arr::get($data, "contrato.prestacion_id", $this->prestaciones->random(3)->pluck("id"))
        ]);
    }

    private function setDatosProveedorEmpresa(&$data)
    {
        $this->setDatosProveedor($data);

        Arr::set($data, "general", [
            "tipo_id" => 2,
            "nit" => Arr::get($data, "general.nit", $this->faker->numerify("###########")),
            "nombre" => Arr::get($data, "general.nombre", $this->faker->text(25)),
            "regional_id" => Arr::get($data, "general.regional_id", 1)
        ]);
    }

    private function setDatosProveedorMedico(&$data)
    {
        $this->setDatosProveedor($data);
        $ci = $this->faker->numerify("########");
        Arr::set($data, "general", [
            "tipo_id" => 1,
            "nit" => Arr::get($data, "general.nit", $ci . "016"),
            "ci" => Arr::get($data, "general.ci", $ci),
            "apellido_paterno" => Arr::get($data, "general.apellido_paterno", $this->faker->lastName),
            "apellido_materno" => Arr::get($data, "general.apellido_materno", $this->faker->lastName),
            "nombres" => Arr::get($data, "general.nombres", $this->faker->name),
            "especialidad_id" =>  $this->especialidad->id,
            "regional_id" => Arr::get($data, "general.regional_id", 1)
        ]);
    }
    
    private function assertProveedor($proveedor, $data){
        $this->assertTrue(!!$proveedor);
        $this->assertTrue($proveedor->tipo_id == Arr::get($data, "general.tipo_id"));
        $this->assertTrue($proveedor->nit == Arr::get($data, "general.nit"));
        if($proveedor->tipo_id == 1){
            $this->assertTrue($proveedor->ci == Arr::get($data, "general.ci"));
            $this->assertTrue($proveedor->ci_complemento == Arr::get($data, "general.ci_complemento"));
            $this->assertTrue($proveedor->apellido_paterno == Arr::get($data, "general.apellido_paterno"));
            $this->assertTrue($proveedor->apellido_materno == Arr::get($data, "general.apellido_materno"));
            $this->assertTrue($proveedor->nombres == Arr::get($data, "general.nombres"));
            $this->assertTrue($proveedor->especialidad_id == Arr::get($data, "general.especialidad_id"));
        }
        else {
            $this->assertTrue($proveedor->nombre == Arr::get($data, "general.nombre"));
        }

        $this->assertTrue($proveedor->regional_id == Arr::get($data, "general.regional_id"));
        $this->assertTrue($proveedor->municipio_id == Arr::get($data, "contacto.municipio"));
        $this->assertTrue($proveedor->direccion == Arr::get($data, "contacto.direccion"));
        $this->assertTrue($proveedor->ubicacion == (Arr::get($data, "contacto.ubicacion") ?
            new Point(Arr::get($data, "contacto.ubicacion.latitud"), Arr::get($data, "contacto.ubicacion.longitud")) :
            null)
        );
        $this->assertTrue($proveedor->telefono1 == Arr::get($data, "contacto.telefono1"));
        $this->assertTrue($proveedor->telefono2 == Arr::get($data, "contacto.telefono2"));

        $contrato = $proveedor->contrato;
        $this->assertTrue(!!$contrato);
        $this->assertTrue($contrato->inicio == Arr::get($data, "contrato.inicio"));
        $this->assertTrue($contrato->fin == Arr::get($data, "contrato.fin"));

        $prestaciones = $contrato->prestaciones;
        $this->assertNotEmpty($prestaciones);
        $this->assertTrue($prestaciones->count() == count(Arr::get($data, "contrato.prestacion_ids", []))
            && $prestaciones->pluck("id")->diff(collect(Arr::get($data, "contrato.prestacion_ids", [])))->isEmpty()
        );
    }

    public function test_usuario_puede_registrar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_PROVEEDORES,
            ])
            ->create();

        //Test registro de especialista
        $data = [];
        $this->setDatosProveedorMedico($data);
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);
        $content = $response->getContent();
        $response->assertOk();
        $id = json_decode($content)->id;

        $proveedor = Proveedor::find($id);
        $this->assertProveedor($proveedor, $data);

        //Test registro de empresa
        $data = [];
        $this->setDatosProveedorEmpresa($data);
        
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);
        $content = $response->getContent();
        $response->assertOk();
        $id = json_decode($content)->id;

        $proveedor = Proveedor::find($id);
        $this->assertProveedor($proveedor, $data);
        
        //Test registro de especialista en otra regional
        $data = [
            "general" => [
                "regional_id" => 3
            ]
        ];
        $this->setDatosProveedorMedico($data);
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);
        $content = $response->getContent();
        $response->assertOk();
        $id = json_decode($content)->id;

        $proveedor = Proveedor::find($id);
        $this->assertProveedor($proveedor, $data);

        //Test registro de empresa en otra regional
        $data = [
            "general" => [
                "regional_id" => 3
            ]
        ];
        $this->setDatosProveedorEmpresa($data);
        
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);
        $content = $response->getContent();
        $response->assertOk();
        $id = json_decode($content)->id;

        $proveedor = Proveedor::find($id);
        $this->assertProveedor($proveedor, $data);
    }

    public function test_usuario_puede_registrar_regionalmente()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_PROVEEDORES_REGIONAL,
            ])
            ->create();

        //Test registro de especialista
        $data = [];
        $this->setDatosProveedorMedico($data);
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);
        $content = $response->getContent();
        $response->assertOk();
        $id = json_decode($content)->id;

        $proveedor = Proveedor::find($id);
        $this->assertProveedor($proveedor, $data);

        //Test registro de empresa
        $data = [];
        $this->setDatosProveedorEmpresa($data);
        
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);
        $content = $response->getContent();
        $response->assertOk();
        $id = json_decode($content)->id;

        $proveedor = Proveedor::find($id);
        $this->assertProveedor($proveedor, $data);
        
        //Test registro de especialista en otra regional
        $data = [
            "general" => [
                "regional_id" => 3
            ]
        ];
        $this->setDatosProveedorMedico($data);
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);
        $response->assertForbidden();

        //Test registro de empresa en otra regional
        $data = [
            "general" => [
                "regional_id" => 3
            ]
        ];
        $this->setDatosProveedorEmpresa($data);
        
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);
        $content = $response->getContent();
        $response->assertForbidden();
    }

    public function test_campos_requeridos(){
        $user = $this->getSuperUser();

        $response = $this->actingAs($user)
        ->postJson("/api/proveedores", [
            "general" => [
                "tipo_id" => 1
            ]
        ]);
        
        $response->assertJsonValidationErrors([
            "general.ci" => "Este campo es requerido",
            "general.apellido_paterno" => "Debe indicar al menos un apellido",
            "general.apellido_materno" => "Debe indicar al menos un apellido",
            "general.nombres" => "Este campo es requerido",
            "general.especialidad_id" => "Este campo es requerido",
            "general.regional_id" => "Este campo es requerido",
            "contrato.inicio" => "Este campo es requerido",
            "contrato.prestacion_ids" => "Este campo es requerido"
        ]);

        $response = $this->postJson("/api/proveedores", [
            "general" => [
                "tipo_id" => 2
            ]
        ]);
        
        $response->assertJsonValidationErrors([
            "general.nombre" => "Este campo es requerido",
            "general.regional_id" => "Este campo es requerido",
            "contrato.inicio" => "Este campo es requerido",
            "contrato.prestacion_ids" => "Este campo es requerido"
        ]);
    }

    // function test_informacion_de_contacto_incompleta()
    // {

    // }
}
