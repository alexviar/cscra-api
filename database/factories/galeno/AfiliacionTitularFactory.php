<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use Illuminate\Database\Eloquent\Factories\Factory;

class AfiliacionTitularFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AfiliacionTitular::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition($attributes)
    {
        $fechaI = $this->faker->date();
        return [
          "ID" => $this->faker->unique()->numerify("AA#############"),
          "FECHA_I_REG_TIT" => $fechaI,
          "REG_LOGIN" => substr($this->faker->username(), 0, 15),
          "REG_DATE" => $this->faker->dateTimeBetween($fechaI, "now"),
          "ESTADO" => 1,
          "ID_EPR" => $attributes["ID_EPR"] ?? Empleador::factory(),
          "ID_AFO" => $attributes["ID_AFO"] ?? Afiliado::factory(["TIPO_AFI" => 1])
        ];
    }
}
