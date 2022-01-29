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
    public function definition($attributes)
    {
        $tipo = $attributes["tipo"] ?? $this->faker->randomElement([1,2]);
        if($tipo == 1){
            $ci = explode("-", $this->faker->unique()->regexify("([1-9][0-9]{7})-([1-9][A-Z]){0,1}"));
            return [
                "tipo" => $tipo,
                "nit" => $attributes["nit"] ?? $this->faker->unique()->numerify("###########"),
                "ci" => $attributes["ci"] ?? new CarnetIdentidad(intval($ci[0]), $ci[1]),
                "apellido_paterno" => $attributes["apellido_paterno"] ?? $this->faker->lastName,
                "apellido_materno" => $attributes["apellido_materno"] ?? $this->faker->lastName,
                "nombre" => $attributes["nombre"] ?? $this->faker->firstName,
                "especialidad" => $attributes["especialidad"] ?? $this->faker->text(25),
                "regional_id" => $attributes["regional_id"] ?? $this->faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]),
                "ubicacion" => $attributes["ubicacion"] ?? new Point($this->faker->latitude, $this->faker->longitude),
                "direccion" => $attributes["direccion"] ?? $this->faker->address,
                "telefono1" => $attributes["telefono1"] ?? intval($this->faker->numerify("########")),
                "telefono2" => $attributes["telefono2"] ?? intval($this->faker->optional()->numerify("########")),
                "estado" => $attributes["estado"] ?? 1
            ];
        }
        else{
            return [
                "tipo" => $tipo,
                "nit" => $attributes["nit"] ?? $this->faker->unique()->numerify("###########"),
                "nombre" => $attributes["nombre"] ?? $this->faker->company,
                "regional_id" => $attributes["regional_id"] ?? $this->faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]),
                "ubicacion" => $attributes["ubicacion"] ?? new Point($this->faker->latitude, $this->faker->longitude),
                "direccion" => $attributes["direccion"] ?? $this->faker->address,
                "telefono1" => $attributes["telefono1"] ?? intval($this->faker->numerify("########")),
                "telefono2" => $attributes["telefono2"] ?? intval($this->faker->optional()->numerify("########")),
                "estado" => $attributes["estado"] ?? 1
            ];
        }
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

    public function medico()
    {                
        // $ci = explode("-", $this->faker->unique()->regexify("([1-9][0-9]{7})-([1-9][A-Z]){0,1}"));
        return $this->state([
            "tipo" => 1,
            // "nit" => $this->faker->unique()->numerify("###########"),
            // "ci" => new CarnetIdentidad(intval($ci[0]), $ci[1]),
            // "apellido_paterno" => $this->faker->lastName,
            // "apellido_materno" => $this->faker->lastName,
            // 'nombre' => $this->faker->name,
            // "especialidad" => $this->faker->text(25),
        ]);
    }

    public function empresa()
    {        
        return $this->state([
            "tipo" => 2,
            // "nit" => $this->faker->unique()->numerify("###########"),
            // "nombre" => $this->faker->company
        ]);
    }
}
