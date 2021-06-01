<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Proveedor;
use App\Models\Permisos;
use App\Models\Prestacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
            echo "\nInitialized\n";
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

    public function test_usuario_puede_registrar_regionalmente()
    {
        $this->assertTrue(true);
    }
}
