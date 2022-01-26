<?php

namespace Tests\Unit;

use App\Infrastructure\SolicitudAtencionExternaQrSigner;
use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use App\Models\Medico;
use App\Models\PrestacionSolicitada;
use App\Models\Proveedor;
use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

use function PHPUnit\Framework\assertTrue;

class SolicitudAtencionExternaTest extends TestCase
{
    
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_encode_qr_data()
    {
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

            
        $user = User::factory()->superUser()->create();
        $proveedor = Proveedor::factory()->tipoRandom()->create();
        $medico = Medico::factory()->create();

        /** @var SolicitudAtencionExterna $solicitud */
        $solicitud = SolicitudAtencionExterna::factory()
            ->for($user, "registradoPor")
            ->for($proveedor)
            ->for($medico)
            ->for($empleador)
            ->for($asegurado, "paciente")
            ->create();

        $encoded_qr_data = (new SolicitudAtencionExternaQrSigner())->sign($solicitud, config("app.private_ec_key"));

        assertTrue((new SolicitudAtencionExternaQrSigner())->validate($encoded_qr_data, config("app.public_ec_key")));

    }
}
