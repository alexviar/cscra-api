<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\AmpliacionPrestacion;
use Carbon\Carbon;
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
    public function definition()
    {
        return [
            "ID" => $this->faker->unique()->numerify("AA#############"),
            "REG_DATE" => $this->faker->date(),
            "REG_LOGIN" => substr($this->faker->username(), 0, 15)
        ];
    }

    public function vencidaAyer(){
        return $this->state([
            "FECHA_EXTINSION_AMP" => Carbon::now()->subDay()
        ]);
    }

    public function vencidaHoy(){
        return $this->state([
            "FECHA_EXTINSION_AMP" => Carbon::now()
        ]);
    }

    public function vencidaManiana(){
        return $this->state([
            "FECHA_EXTINSION_AMP" => Carbon::now()->addDay()
        ]);
    }
}
