<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\AfiliacionBeneficiario;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AfiliacionBeneficiarioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AfiliacionBeneficiario::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $fechaI = $this->faker->date();
        return [
            "ID" => $this->faker->unique()->numerify("AA#############"),
            "FECHA_INGRESO_BEN" => $fechaI,
            "REG_LOGIN" => substr($this->faker->username(), 0, 15),
            "REG_DATE" => $this->faker->dateTimeBetween($fechaI, "now"),
        ];
    }

    public function derechohabiente(){
        return $this->state([
            "PARENTESCO_BEN" => 8
        ]);
    }

    public function fechaExtinsionVencidaAyer(){
        return $this->state([
            "FECHA_EXTINSION_BEN" => Carbon::now()->subDay()
        ]);
    }

    public function fechaExtinsionVencidaHoy(){
        return $this->state([
            "FECHA_EXTINSION_BEN" => Carbon::now()
        ]);
    }

    public function fechaExtinsionVencidaManiana(){
        return $this->state([
            "FECHA_EXTINSION_BEN" => Carbon::now()->addDay()
        ]);
    }
}
