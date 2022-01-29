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
use Illuminate\Support\Facades\Storage;
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
        $login = User::factory()
            ->withPermissions([
                Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA
            ])
            ->create();

        $solicitud = SolicitudAtencionExterna::factory()->create();
        $numero = $solicitud->numero;
        $this->beforeApplicationDestroyed(function() use($numero){
            Storage::delete("formularios/dm11/$numero.pdf");
        });

        $response = $this->actingAs($login)
            ->get("/api/formularios/dm11/$numero");    
        $response->assertOk();
        Storage::disk("local")->assertExists("formularios/dm11/$numero.pdf");
        // $content = $response->streamedContent();
        // $this->assertTrue(!!$content);
    }
    
    public function test_usuario_con_permiso_para_emitir_dm11_restringido_por_regional()
    {
        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
            ])
            ->create();

        $solicitud = SolicitudAtencionExterna::factory()->regionalLaPaz()->create();
        $numero = $solicitud->numero;
        $this->beforeApplicationDestroyed(function() use($numero){
            Storage::delete("formularios/dm11/$numero.pdf");
        });

        $response = $this->actingAs($login)
            ->get("/api/formularios/dm11/$numero");    
        $response->assertOk();
        Storage::disk("local")->assertExists("formularios/dm11/$numero.pdf");

        $solicitud = SolicitudAtencionExterna::factory()
            ->regionalSantaCruz()
            ->create();
        
        $numero = $solicitud->numero;
        $response = $this->actingAs($login)
            ->get("/api/formularios/dm11/$numero");
        $response->assertForbidden();

        $login = User::factory()
            ->regionalLaPaz()
            ->withPermissions([
                Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA,
                Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL
            ])
            ->create();
        
        $numero = $solicitud->numero;
        $response = $this->actingAs($login)
            ->get("/api/formularios/dm11/$numero");
        $response->assertForbidden();
    }

    public function test_usuario_sin_permiso()
    {
        $user = User::factory()
            ->withPermissions([])
            ->create();

        $solicitud = SolicitudAtencionExterna::factory()->create();
        $numero = $solicitud->numero;

        $response = $this->actingAs($user)
            ->get("/api/formularios/dm11/$numero");

        $response->assertForbidden();
    }

    public function test_super_usuario()
    {
        //Storage::fake("local");
        $user = User::factory()
            ->superUser()
            ->create();

        $solicitud = SolicitudAtencionExterna::factory()->create();
        $numero = $solicitud->numero;

        $this->beforeApplicationDestroyed(function() use($numero){
            Storage::delete("formularios/dm11/$numero.pdf");
        });

        $response = $this->actingAs($user)->get("/api/formularios/dm11/$numero");
        $response->assertOk();
        Storage::disk("local")->assertExists("formularios/dm11/$numero.pdf");
        // $content = $response->streamedContent();
        // $this->assertTrue(!!$content);
    }
}
