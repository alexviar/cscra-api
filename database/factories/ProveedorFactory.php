<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Proveedor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "regional_id" => 1
        ];
    }

    public function regionalLaPaz()
    {
        return $this->state([
            "regional_id" => 1
        ]);
    }

    public function regionalSantaCruz()
    {
        return $this->state([
            "regional_id" => 3
        ]);
    }

    public function medico()
    {        
        $ci = explode("-", $this->faker->unique()->regexify("[0-9]{7,8}-[A-Z][0-9]"));
        return $this->state([
            "tipo_id" => 1,
            "nit" => $this->faker->unique()->numerify("###########"),
            "ci" => $ci[0],
            "ci_complemento" => $ci[1],
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            "nombres" => $this->faker->name
        ]);
    }

    public function empresa()
    {        
        return $this->state([
            "tipo_id" => 2,
            "nit" => $this->faker->numerify("###########"),
            "nombre" => $this->faker->text($this->faker->numberBetween(10, 80))
        ]);
    }
}
