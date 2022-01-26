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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VerDm11Test extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_con_permiso_para_emitir_dm11()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA
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
            ->create();
        $proveedor = Proveedor::factory()
            ->empresa()
            ->create();

        $solicitud = SolicitudAtencionExterna::factory()
            ->for($afiliado, "asegurado")
            ->for($afiliado->empleador)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create();

        $numero = $solicitud->numero;
        $response = $this->actingAs($user)
            ->get("/api/formularios/dm11/$numero");

        $response->assertOk();
        // $content = $response->streamedContent();
        // $this->assertTrue(!!$content);
    }
    
    public function test_usuario_con_permiso_para_emitir_dm11_restringido_por_regional()
    {
        $user = User::factory()
            ->withPermissions([
                Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
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
            ->create();
        $proveedor = Proveedor::factory()
            ->empresa()
            ->create();

        $solicitud1 = SolicitudAtencionExterna::factory()
            ->for($afiliado, "asegurado")
            ->for($afiliado->empleador)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create();


        $numero = $solicitud1->numero;
        $response = $this->actingAs($user)
            ->get("/api/formularios/dm11/$numero");
    
        $response->assertOk();
        
        $solicitud2 = SolicitudAtencionExterna::factory()
            ->regionalSantaCruz()
            ->for($afiliado, "asegurado")
            ->for($afiliado->empleador)
            ->for($medico)
            ->for($proveedor)
            ->for($user, "registradoPor")
            ->create();
        
        $numero = $solicitud2->numero;
        $response = $this->actingAs($user)
            ->get("/api/formularios/dm11/$numero");

        $response->assertForbidden();
    }
}
