<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\AfiliacionBeneficiario;
use App\Models\Galeno\AmpliacionPrestacion;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class AmpliacionPrestacionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AmpliacionPrestacion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition($attributes)
    {
        $fecha = CarbonImmutable::make($this->faker->date);
        return [
            "ID" => $this->faker->unique()->numerify("AA#############"),
            "ID_BNO" => $attributes["ID_BNO"] ?? AfiliacionBeneficiario::factory()->create()->extinguible($fecha),
            "FECHA_EXTINSION_AMP" => $attributes["FECHA_EXTINSION_AMP"] ?? $fecha->addYears(5),
            "REG_DATE" => $this->faker->date(),
            "REG_LOGIN" => substr($this->faker->username(), 0, 15)
        ];
    }

    public function vencimiento($fecha){
        return $this->state([
            "FECHA_EXTINSION_AMP" => $fecha
        ]);
    }
}
