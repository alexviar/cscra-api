<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\AfiliacionTitular;
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
    public function definition()
    {
        $fechaI = $this->faker->date();
        return [
          "ID" => $this->faker->unique()->numerify("AA#############"),
          "FECHA_I_REG_TIT" => $fechaI,
          "REG_LOGIN" => substr($this->faker->username(), 0, 15),
          "REG_DATE" => $this->faker->dateTimeBetween($fechaI, "now"),
          "ESTADO" => 1
        ];
    }
}
