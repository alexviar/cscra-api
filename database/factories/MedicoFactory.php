<?php

namespace Database\Factories;

use App\Models\Medico;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Medico::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition($attributes)
    {
        $ci = explode("-", $this->faker->unique()->regexify("([1-9][0-9]{7})-([1-9][A-Z]){0,1}"));
        return [
            // "id" => $this->faker->unique()->randomNumber(),
            "ci" => $attributes["ci"] ?? new CarnetIdentidad(intval($ci[0]), $ci[1]),
            "apellido_paterno" => $attributes["apellido_paterno"] ?? $this->faker->lastName,
            "apellido_materno" => $attributes["apellido_materno"] ?? $this->faker->lastName,
            'nombre' => $attributes["nombre"] ?? $this->faker->name,
            "especialidad" => $attributes["especialidad"] ?? $this->faker->text(25),
            'regional_id' => $attributes["regional_id"] ?? $this->faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]),
            'estado' => $attributes["estado"] ?? 1,//$this->faker->randomElement([1,2]),
        ];
    }

    public function baja(){
        return $this->state([
            "estado" => 2
        ]);
    }

    public function regionalLaPaz(){
        return $this->state([
            "regional_id" => 1
        ]);
    }

    public function regionalSantaCruz(){
        return $this->state([
            "regional_id" => 3
        ]);
    }
}
