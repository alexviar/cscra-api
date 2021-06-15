<?php

namespace Tests\Feature;

use App\Models\ContratoProveedor;
use App\Models\Proveedor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuscarProveedoresTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_buscar_proveedores_activos()
    {
        $user = $this->getSuperUser();



        $proveedorSinContrato = Proveedor::factory()
            ->empresa()
            ->create();
        $proveedorContratoAnulado = Proveedor::factory()
            ->empresa()
            ->has(ContratoProveedor::factory()->anulado(), "contrato")
            ->create();
        
        $proveedorContratoAnulado2 = Proveedor::factory()
            ->empresa()
            ->has(ContratoProveedor::factory()->iniciaHoy(), "contratos")
            ->create();

        ContratoProveedor::factory()
            ->iniciaHoy()
            ->for($proveedorContratoAnulado2)
            ->anulado()
            ->create();
        
        $proveedorFuturo = Proveedor::factory()
            ->empresa()
            ->has(ContratoProveedor::factory()->iniciaManiana(), "contratos")
            ->create();

        $proveedorConExtension = Proveedor::factory()
            ->empresa()
            ->has(ContratoProveedor::factory()->state(["extension" => Carbon::now()]), "contratos")
            ->create();

        $proveedorConExtensionVencida = Proveedor::factory()
            ->empresa()
            ->has(ContratoProveedor::factory()->state(["extension" => Carbon::now()->subDay()]), "contratos")
            ->create();

        $proveedorFinalizaHoy = Proveedor::factory()
            ->empresa()
            ->has(ContratoProveedor::factory()->finalizaHoy(), "contratos")
            ->create();
        $proveedorFinalizo = Proveedor::factory()
            ->empresa()
            ->has(ContratoProveedor::factory()->finalizoAyer(), "contratos")
            ->create();
        $proveedorTiempoIndefinido = Proveedor::factory()
            ->empresa()
            ->has(ContratoProveedor::factory()->indefinido(), "contratos")
            ->create();
        // dd(ContratoProveedor::get()->toArray());

        $queryParams = [
            "filter" => [
                "activos" => 1
            ]
        ];  

        $response = $this->actingAs($user)->getJson("/api/proveedores?".http_build_query($queryParams));
        $response->assertOk();
        $array = collect(json_decode($response->getContent()))->pluck('id');

        $idsActivos = collect([
            $proveedorContratoAnulado2->id,
            $proveedorConExtension->id,
            $proveedorFinalizaHoy->id,
            $proveedorTiempoIndefinido->id
        ]);
        $this->assertTrue($array->contains($proveedorConExtension->id));
        $this->assertTrue($array->contains($proveedorContratoAnulado2->id));
        $this->assertTrue($array->contains($proveedorFinalizaHoy->id));
        $this->assertTrue($array->contains($proveedorTiempoIndefinido->id));
        $this->assertTrue($array->count() == 4);
    }
}
