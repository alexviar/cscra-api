<?php

namespace Tests\Feature\SolicitudAtencionExterna;

use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use App\Models\Medico;
use App\Models\Permisos;
use App\Models\Proveedor;
use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuscarSolicitudAtencionExternaTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_con_permiso_para_buscar()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA
            ])
            ->create();

        $empleador = Empleador::factory()->create();
        $afiliado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($afiliado)
            ->for($empleador)
            ->create()
            ->refresh();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();
        $proveedor = Proveedor::factory()
            ->empresa()
            ->regionalLaPaz()
            ->create();
        $proveedorMedico = Proveedor::factory()
            ->medico()
            ->regionalLaPaz() 
            ->create();

        $solicitud1 = SolicitudAtencionExterna::factory()
            ->for($afiliado, "paciente")
            ->for($afiliado->empleador)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create()
            ->refresh();


        $solicitud2 = SolicitudAtencionExterna::factory()
            ->for($afiliado, "paciente")
            ->for($afiliado->empleador)
            ->for($medico)
            ->for($proveedorMedico)
            ->for($user, "registradoPor")
            ->create()
            ->refresh();

        $response = $this->actingAs($user)
            ->getJson("/api/solicitudes-atencion-externa");

        $response->assertOk();
        // $response->assertJson([
        //     "meta" => [
        //         "total" => 2
        //     ],
        //     "records" => [
        //         [
        //             "id" => $solicitud1->id,
        //             "numero" => $solicitud1->numero,
        //             "fecha" => $solicitud1->fecha->format("d/m/y H:i:s"),
        //             "paciente" => [
        //                 "id" => $afiliado->id,
        //                 "matricula" => $afiliado->matricula
        //             ],
        //             "medico" => $solicitud1->medico, //"Paterno Materno Nombres",
        //             "proveedor" => $solicitud1->proveedor, //"Nombre Empresa",
        //             "url_dm11" => $solicitud1->url_dm11
        //         ],
        //         [
        //             "id" => $solicitud2->id,
        //             "numero" => $solicitud2->numero,
        //             "fecha" => $solicitud2->fecha->format("d/m/y H:i:s"),
        //             "paciente" => [
        //                 "id" => $afiliado->id,
        //                 "matricula" => $afiliado->matricula
        //             ],
        //             "medico" => $solicitud2->medico, //"Paterno Materno Nombres",
        //             "proveedor" => $solicitud2->proveedor, //"Paterno Materno Proveedor",
        //             "url_dm11" => $solicitud2->url_dm11
        //         ]
        //     ]
        // ]);
    }

    public function test_usuario_con_permiso_para_buscar_restringido_por_regional()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
            ])
            ->create();

        $empleador = Empleador::factory()->create();
        $afiliado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($afiliado)
            ->for($empleador)
            ->create()
            ->refresh();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();
        $proveedor = Proveedor::factory()
            ->empresa()
            ->regionalLaPaz()
            ->create();

        $solicitud = SolicitudAtencionExterna::factory()
            ->for($afiliado, "paciente")
            ->for($afiliado->empleador)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create()
            ->refresh();

        $solicitudRegionalSantaCruz = SolicitudAtencionExterna::factory()
            ->regionalSantaCruz()
            ->for($afiliado, "paciente")
            ->for($afiliado->empleador)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create()
            ->refresh();

        $this->assertDatabaseCount(SolicitudAtencionExterna::class, 2);

        $response = $this->actingAs($user)
            ->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
                "filter" => [
                    "regional_id" => 1
                ]
            ]));
        $response->assertOk();
        // $response->assertJson([
        //     "meta" => [
        //         "total" => 1
        //     ],
        //     "records" => [
        //         [
        //             "id" => $solicitud->id,
        //             "numero" => $solicitud->numero,
        //             "fecha" => $solicitud->fecha->format("d/m/y H:i:s"),
        //             "paciente" => [
        //                 "id" => $afiliado->id,
        //                 "matricula" => $afiliado->matricula
        //             ],
        //             "medico" => $solicitud->medico, //"Paterno Materno Nombres",
        //             "proveedor" => $solicitud->proveedor, //"Nombre Empresa",
        //             "url_dm11" => $solicitud->url_dm11
        //         ]
        //     ]
        // ]);

        $response = $this->actingAs($user)
            ->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
                "filter" => [
                    "regional_id" => 3
                ]
            ]));

        $response->assertForbidden();

        $response = $this->actingAs($user)
            ->getJson("/api/solicitudes-atencion-externa");

        $response->assertForbidden();
    }

    public function test_buscar_por_empleador()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA
            ])
            ->create();

        $empleador1 = Empleador::factory()->create();
        $afiliado1 = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($afiliado1)
            ->for($empleador1)
            ->create()
            ->refresh();
        $empleador2 = Empleador::factory()->create();
        $afiliado2 = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($afiliado2)
            ->for($empleador2)
            ->create()
            ->refresh();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();
        $proveedor = Proveedor::factory()
            ->empresa()
            ->regionalLaPaz()
            ->create();

        $solicitudes1 = SolicitudAtencionExterna::factory()
            ->count(2)
            ->for($afiliado1, "paciente")
            ->for($empleador1)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create();

        $solicitudes2 = SolicitudAtencionExterna::factory()
            ->count(2)
            ->regionalSantaCruz()
            ->for($afiliado2, "paciente")
            ->for($afiliado2->empleador)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create();

        $this->assertDatabaseCount("atenciones_externas", 4);

        
        $response = $this->actingAs($user)
            ->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
                "filter" => [
                    "numero_patronal" => $empleador1->numero_patronal
                ]
            ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 2
            ],
            "records" => $solicitudes1->map(function($solicitud) use($afiliado1, $medico, $proveedor) {
                $solicitud->refresh();
                return [
                    "id" => $solicitud->id,
                    "numero" => $solicitud->numero,
                    "fecha" => $solicitud->fecha->format("d/m/y H:i:s"),
                    "paciente" => [
                        "id" => $afiliado1->id,
                        "matricula" => $afiliado1->matricula
                    ],
                    "medico" => $medico,
                    "proveedor" => $proveedor,
                    "url_dm11" => $solicitud->url_dm11
                ];
            })->toArray()
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
                "filter" => [
                    "numero_patronal" => $empleador2->numero_patronal
                ]
            ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 2
            ],
            "records" => $solicitudes2->map(function($solicitud) use($afiliado2, $medico, $proveedor) {
                $solicitud->refresh();
                return [
                    "id" => $solicitud->id,
                    "numero" => $solicitud->numero,
                    "fecha" => $solicitud->fecha->format("d/m/y H:i:s"),
                    "paciente" => [
                        "id" => $afiliado2->id,
                        "matricula" => $afiliado2->matricula
                    ],
                    "medico" => $medico,
                    "proveedor" => $proveedor,
                    "url_dm11" => $solicitud->url_dm11
                ];
            })->toArray()
        ]);
    }

    
    public function test_buscar_por_asegurado()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA
            ])
            ->create();

        $empleador1 = Empleador::factory()->create();
        $afiliado1 = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($afiliado1)
            ->for($empleador1)
            ->create()
            ->refresh();
        $empleador2 = Empleador::factory()->create();
        $afiliado2 = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($afiliado2)
            ->for($empleador2)
            ->create()
            ->refresh();

        $medico = Medico::factory()
            ->regionalLaPaz()
            ->create();
        $proveedor = Proveedor::factory()
            ->empresa()
            ->regionalLaPaz()
            ->create();

        $solicitudes1 = SolicitudAtencionExterna::factory()
            ->count(2)
            ->for($afiliado1, "paciente")
            ->for($empleador1)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create();

        $solicitudes2 = SolicitudAtencionExterna::factory()
            ->count(2)
            ->regionalSantaCruz()
            ->for($afiliado2, "paciente")
            ->for($afiliado2->empleador)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create();

        $this->assertDatabaseCount("atenciones_externas", 4);
        
        $response = $this->actingAs($user)
            ->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
                "filter" => [
                    "matricula_asegurado" => $afiliado1->matricula
                ]
            ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 2
            ],
            "records" => $solicitudes1->map(function($solicitud) use($afiliado1, $medico, $proveedor) {
                $solicitud->refresh();
                return [
                    "id" => $solicitud->id,
                    "numero" => $solicitud->numero,
                    "fecha" => $solicitud->fecha->format("d/m/y H:i:s"),
                    "paciente" => [
                        "id" => $afiliado1->id,
                        "matricula" => $afiliado1->matricula
                    ],
                    "medico" => $medico,
                    "proveedor" => $proveedor,
                    "url_dm11" => $solicitud->url_dm11
                ];
            })->toArray()
        ]);

        
        $response = $this->actingAs($user)
            ->getJson("/api/solicitudes-atencion-externa?" . http_build_query([
                "filter" => [
                    "matricula_asegurado" => $afiliado2->matricula
                ]
            ]));
        $response->assertOk();
        $response->assertJson([
            "meta" => [
                "total" => 2
            ],
            "records" => $solicitudes2->map(function($solicitud) use($afiliado2, $medico, $proveedor) {
                $solicitud->refresh();
                return [
                    "id" => $solicitud->id,
                    "numero" => $solicitud->numero,
                    "fecha" => $solicitud->fecha->format("d/m/y H:i:s"),
                    "paciente" => [
                        "id" => $afiliado2->id,
                        "matricula" => $afiliado2->matricula
                    ],
                    "medico" => $medico,
                    "proveedor" => $proveedor,
                    "url_dm11" => $solicitud->url_dm11
                ];
            })->toArray()
        ]);
    }

    public function test_usuario_sin_permiso()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/solicitudes-atencion-externa");
        $response->assertForbidden();
    }

    public function test_guest()
    {
        $response = $this->getJson("/api/solicitudes-atencion-externa");
        $response->assertUnauthorized();
    }
}
