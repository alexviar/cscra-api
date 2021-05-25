<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\BajaAfiliacion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BajaAfiliacionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BajaAfiliacion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "ID" => $this->faker->unique()->numerify("AA#############"),
            "FECHA_PRESENTACION_BAJ" => $this->faker->date(),
            "FECHA_REG_BAJ" => $this->faker->date(),
            "ESTADO" => 1
        ];
    }

    public function validezVencidaAyer(){
        return $this->state([
            "FECHA_VALIDEZ_SEGURO_BAJ" => Carbon::now()->subDay()
        ]);
    }

    public function validezVencidaHoy(){
        return $this->state([
            "FECHA_VALIDEZ_SEGURO_BAJ" => Carbon::now()
        ]);
    }

    public function validezVencidaManiana(){
        return $this->state([
            "FECHA_VALIDEZ_SEGURO_BAJ" => Carbon::now()->addDay()
        ]);
    }
}
