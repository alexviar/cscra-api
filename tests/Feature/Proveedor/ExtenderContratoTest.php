<?php

namespace Tests\Feature\Proveedor;

use App\Models\ContratoProveedor;
use App\Models\Permisos;
use App\Models\Proveedor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExtenderContratoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_extender_contrato()
    {
        $usuario = User::factory()
            ->withPermissions([
                Permisos::EXTENDER_CONTRATO_PROVEEDOR
            ])
            ->create();

        $proveedor = Proveedor::factory()->empresa()->create();
        $contrato = ContratoProveedor::factory()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertStatus(400);

        $this->travelTo($contrato->fin);
        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertOk();
        $contrato->refresh();
        $extension = $contrato->extension;
        $this->assertTrue($extension->eq($contrato->fin->addWeek()));

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertStatus(400);

        $this->travelTo($extension);
        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertOk();
        $contrato->refresh();
        $this->assertTrue($contrato->extension->eq($extension->addWeek()));

        //Consumible
        $proveedor = Proveedor::factory()->empresa()->create();
        $contrato = ContratoProveedor::factory()->indefinido()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertStatus(400);

        $contrato->estado = 2;
        $contrato->save();
        $now = Carbon::now();
        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertOk();
        $contrato->refresh();
        $extension = $contrato->extension;
        $this->assertTrue($extension->eq($now->addWeek()));

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertStatus(400);

        $this->travelTo($extension);
        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertOk();
        $contrato->refresh();
        $this->assertTrue($contrato->extension->eq($extension->addWeek()));

    }
    
    public function test_usuario_puede_extender_contrato_regionalmente()
    {
        $usuario = User::factory()
            ->withPermissions([
                Permisos::EXTENDER_CONTRATO_PROVEEDOR_REGIONAL
            ])
            ->create();

        $proveedor = Proveedor::factory()->empresa()->create();
        $contrato = ContratoProveedor::factory()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertStatus(400);

        $proveedor = Proveedor::factory()->regionalSantaCruz()->empresa()->create();
        $contrato = ContratoProveedor::factory()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");
        $response->assertForbidden();
    }
    
    public function test_usuario_sin_permisos()
    {
        $usuario = User::factory()
            ->withPermissions([])
            ->create();

        $proveedor = Proveedor::factory()->empresa()->create();
        $contrato = ContratoProveedor::factory()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/extender");

        $response->assertForbidden();
        $contrato->refresh();
        $this->assertTrue($contrato->extension == null);
    }
}
