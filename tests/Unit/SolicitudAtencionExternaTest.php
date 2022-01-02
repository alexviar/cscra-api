<?php

namespace Tests\Unit;

use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use App\Models\PrestacionSolicitada;
use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class SolicitudAtencionExternaTest extends TestCase
{
    function createSuperUser()
    {
        return User::where("username", "admin")->first();
    }

    public function test_get_qr_data() {
        $this->travelTo(Carbon::create(2020));
        $empleador = Empleador::factory()
            ->create();
        $asegurado = Afiliado::factory()->create();
        AfiliacionTitular::factory()
            ->for($empleador)
            ->for($asegurado)
            ->create();

            
        $user = $this->createSuperUser();

        /** @var SolicitudAtencionExterna $solicitud */
        $solicitud = SolicitudAtencionExterna::factory()
            ->state([
                "fecha" => Carbon::now()
            ])
            ->for($user, "registradoPor")
            ->for($empleador)
            ->for($asegurado, "asegurado")
            ->create();

        $solicitud->prestacionesSolicitadas()->create([
            "prestacion" => "Quimioterapia",
        ]);

        var_dump($solicitud->getQrData());

    }
    
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

            
        $user = $this->createSuperUser();

        /** @var SolicitudAtencionExterna $solicitud */
        $solicitud = SolicitudAtencionExterna::factory()
            ->for($user, "registradoPor")
            ->for($empleador)
            ->for($asegurado, "asegurado")
            ->create();

        $solicitud->prestacionesSolicitadas()->create([
            "prestacion" => "Quimioterapia",
        ]);

        dd($solicitud->getContentArrayAttribute());
    }
}
