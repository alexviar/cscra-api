<?php

namespace Database\Factories;

use App\Models\Medico;
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
    public function definition()
    {
        $ci = explode("-", $this->faker->unique()->regexify("[0-9]{7,8}-[A-Z][0-9]"));
        return [
            // "id" => $this->faker->unique()->randomNumber(),
            "ci" => intval($ci[0]),
            "ci_complemento" => $ci[1],
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name,
            "regional_id" => 1,
            "estado" => 1,
            "tipo" => 1
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

    public function proveedor()
    {
        return [
            "tipo" => 2
        ];
    }
}
