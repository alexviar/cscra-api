<?php

namespace Database\Factories;

use App\Models\Prestacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrestacionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Prestacion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // "id" => $this->faker->unique()->randomNumber(),
            "nombre" => $this->faker->unique()->text(25)
        ];
    }
}
