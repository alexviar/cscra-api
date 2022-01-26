<?php

namespace Database\Factories;

use App\Models\Proveedor;
use App\Models\ValueObjects\CarnetIdentidad;
use Grimzy\LaravelMysqlSpatial\Types\Point;
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
            "regional_id" => $this->faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]),
            "ubicacion" => new Point($this->faker->latitude, $this->faker->longitude),
            "direccion" => $this->faker->address,
            "telefono1" => intval($this->faker->numerify("########")),
            "telefono2" => intval($this->faker->optional()->numerify("########")),
            "estado" => 1
        ];
    }

    public function baja()
    {
        return $this->state([
            "estado" => 2
        ]);
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

    public function tipoRandom()
    {
        return rand(0, 1) ? $this->medico() : $this->empresa();
    }

    public function medico()
    {                
        $ci = explode("-", $this->faker->unique()->regexify("([1-9][0-9]{7})-([1-9][A-Z]){0,1}"));
        return $this->state([
            "tipo" => 1,
            "nit" => $this->faker->unique()->numerify("###########"),
            "ci" => new CarnetIdentidad(intval($ci[0]), $ci[1]),
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            'nombre' => $this->faker->name,
            "especialidad" => $this->faker->text(25),
        ]);
    }

    public function empresa()
    {        
        return $this->state([
            "tipo" => 2,
            "nit" => $this->faker->unique()->numerify("###########"),
            "nombre" => $this->faker->company
        ]);
    }
}
