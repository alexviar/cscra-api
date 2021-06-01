<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Proveedor;
use App\Models\Permisos;
use App\Models\Prestacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class RegistrarProveedorTest extends TestCase
{
    private static $initialized;
    private static $especialidad;
    private static $prestaciones;

    public function setUp(): void
    {
        parent::setUp();

        if(!static::$initialized){
            static::$initialized = true;
            static::$especialidad = Especialidad::factory()->create();
            static::$prestaciones = Prestacion::factory()->count(10)->create();
        }
    }

    public function test_usuario_puede_registrar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_PROVEEDORES,
            ])
            ->create();


        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", [
                "general" => [
                    "tipo_id" => 1,
                    "ci" => 12345678,
                    "apellido_paterno" => "Paterno",
                    "apellido_materno" => "Materno",
                    "nombres" => "Nombre",
                    "especialidad_id" =>  static::$especialidad->id,
                    "regional_id" => 1
                ],
                "contacto" => [
                    "municipio_id" => 1,
                    "direccion" => "Av. Los claveles",
                    "ubicacion" => [
                        "latitud" => 0,
                        "longitud" => 0
                    ],
                    "telefono1" => 60000000
                ],
                "contrato" => [
                    "inicio" => "2020-01-01",
                    "prestacion_ids" => static::$prestaciones->random(3)->pluck("id")
                ]
            ]);

        // dd($response->getContent());
        $response->assertOk();
        $this->assertDatabaseHas("proveedores", [
            "tipo_id" => 1,
            // "ci" => 12345678,
            // "apellido_paterno" => "Paterno",
            // "apellido_materno" => "Materno",
            // "nombres" => "Nombre",
            // "especialidad_id" =>  static::$especialidad->id,
            "regional_id" => 1
        ]);

        
        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", [
                "general" => [
                    "tipo_id" => 2,
                    "nit" => "12345679019",
                    "nombre" => "Nombre",
                    "regional_id" => 1
                ],
                "contacto" => [
                    "municipio_id" => 1,
                    "direccion" => "Av. Los claveles",
                    "ubicacion" => [
                        "latitud" => 0,
                        "longitud" => 0
                    ],
                    "telefono1" => 60000000
                ],
                "contrato" => [
                    "inicio" => "2020-01-01",
                    "prestacion_ids" => static::$prestaciones->random(3)->pluck("id")
                ]
            ]);

        $response->assertOk();
        $this->assertDatabaseHas("proveedores", [
            "tipo_id" => 2,
            "nit" => "12345679019",
            "nombre" => "Nombre",
            // "ci" => 12345679,
            // "apellido_paterno" => "Paterno",
            // "apellido_materno" => "Materno",
            // "nombres" => "Nombre",
            // "especialidad_id" =>  static::$especialidad->id,
            "regional_id" => 1
        ]);
    }

    private function registrarProveedorEmpresa($user, $data=[])
    {
        Arr::set($data, "general", [
            "tipo_id" => 2,
            "nit" => Arr::get($data, "general.nit", "12345679019"),
            "nombre" => Arr::get($data, "general.nombre", "Nombre"),
            "regional_id" => Arr::get($data, "general.regional_id", 1)
        ]);

        if(Arr::has($data, "contacto")){
            Arr::set($data, "contacto", [
                "municipio_id" => Arr::get($data, "contacto.municipio_id", 1),
                "direccion" => Arr::get($data, "contacto.direccion", "Av. Los claveles"),
                "ubicacion" => Arr::get($data, "contacto.ubicacion", [
                    "latitud" => 0,
                    "longitud" => 0
                ]),
                "telefono1" => Arr::get($data, "contacto.telefono1", 70000000)
            ]);
        }

        Arr::set($data, "contrato", [
            "inicio" => Arr::get($data, "contrato.inicio", "2020/01/01"),
            "prestacion_ids" => Arr::get($data, "contrato.prestacion_id", static::$prestaciones->random(3)->pluck("id"))
        ]);

        return $this->postJson("/api/proveedores", $data);
    }

    private function registrarProveedorMedico($user, $data = [])
    {
        Arr::set($data, "general", [
            "tipo_id" => 2,
            "nit" => Arr::get($data, "general.nit", "12345679019"),
            "nombre" => Arr::get($data, "general.nombre", "Nombre"),
            "regional_id" => Arr::get($data, "general.regional_id", 1)
        ]);

        if(Arr::has($data, "contacto")){
            Arr::set($data, "contacto", [
                "municipio_id" => Arr::get($data, "contacto.municipio_id", 1),
                "direccion" => Arr::get($data, "contacto.direccion", "Av. Los claveles"),
                "ubicacion" => Arr::get($data, "contacto.ubicacion", [
                    "latitud" => 0,
                    "longitud" => 0
                ]),
                "telefono1" => Arr::get($data, "contacto.telefono1", 70000000)
            ]);
        }

        Arr::set($data, "contrato", [
            "inicio" => Arr::get($data, "contrato.inicio", "2020/01/01"),
            "prestacion_ids" => Arr::get($data, "contrato.prestacion_ids", static::$prestaciones->random(3)->pluck("id"))
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/proveedores", $data);

        return $response;
    }

    public function test_usuario_puede_registrar_regionalmente()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::REGISTRAR_PROVEEDORES_REGIONAL,
            ])
            ->create();


        $response = $this->registrarProveedorMedico($user);
        
    }
}
