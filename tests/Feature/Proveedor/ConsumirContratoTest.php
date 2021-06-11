<?php

namespace Tests\Feature;

use App\Models\ContratoProveedor;
use App\Models\Permisos;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConsumirContratoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_usuario_puede_consumir_contrato()
    {
        $usuario = User::factory()
            ->withPermissions([
                Permisos::CONSUMIR_CONTRATO_PROVEEDOR
            ])
            ->create();

        $proveedor = Proveedor::factory()->empresa()->create();
        $contrato = ContratoProveedor::factory()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/consumir");
        $response->assertStatus(400);

        $proveedor = Proveedor::factory()->empresa()->create();
        $contrato = ContratoProveedor::factory()->indefinido()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/consumir");
        $response->assertOk();
        $contrato->refresh();        
        $this->assertTrue($contrato->consumido);
    }
    
    public function test_usuario_puede_consumir_contrato_regionalmente()
    {
        $usuario = User::factory()
            ->withPermissions([
                Permisos::CONSUMIR_CONTRATO_PROVEEDOR_REGIONAL
            ])
            ->create();

        $proveedor = Proveedor::factory()->empresa()->create();
        $contrato = ContratoProveedor::factory()->indefinido()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/consumir");
        $response->assertOk();
        $contrato->refresh();
        $this->assertTrue($contrato->consumido);

        $proveedor = Proveedor::factory()->regionalSantaCruz()->empresa()->create();
        $contrato = ContratoProveedor::factory()->indefinido()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/consumir");

        $response->assertForbidden();
    }

    
    public function test_usuario_sin_permisos()
    {
        $usuario = User::factory()
            ->withPermissions([])
            ->create();

        $proveedor = Proveedor::factory()->empresa()->create();
        $contrato = ContratoProveedor::factory()->indefinido()->for($proveedor)->create();

        $response = $this->actingAs($usuario)
                ->putJson("/api/proveedores/{$proveedor->id}/contratos/{$contrato->id}/consumir");

        $response->assertForbidden();
    }
}
