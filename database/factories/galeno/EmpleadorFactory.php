<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\Empleador;
use App\Models\Regional;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpleadorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Empleador::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition($attributes)
    {
        $fechaIngreso = $this->faker->date();
        return [
            "ID" => $this->faker->unique()->numerify("AA#############"),
            "NUMERO_PATRONAL_EMP" => $attributes["NUMERO_PATRONAL_EMP"] ?? $this->faker->unique()->numerify("###-#####"),
            "NOMBRE_EMP" => $attributes["NOMBRE_EMP"] ?? $this->faker->text(50),
            "ESTADO_EMP" => $attributes["ESTADO_EMP"] ?? 1,
            "FECHA_BAJA_EMP" => $attributes["FECHA_BAJA_EMP"] ?? NULL,
            "ID_RAD" => $this->faker->unique()->numerify("AA#############"),
            "ID_RGL" => $attributes["ID_RGL"] ?? $this->faker->randomElement(Regional::LOCAL_ID_TO_GALENO_ID),
            "FECHA_INGRESO_EMP" => $this->faker->date(),
            "REG_DATE" => $this->faker->dateTimeBetween($fechaIngreso, "now"),
            "REG_LOGIN" => substr($this->faker->username(), 0, 15),
            "ID_TAO" => $this->faker->unique()->numerify("AA#############")
        ];
    }

    function regionalLaPaz(){
        return $this->state([
            "ID_RGL" => Regional::mapLocalIdToGalenoId(1)
        ]);
    }    

    function regionalSantaCruz(){
        return $this->state([
            "ID_RGL" => Regional::mapLocalIdToGalenoId(3)
        ]);
    }

    function estadoDesconocido()
    {
        return $this->state([
            "ESTADO_EMP" => 0,
        ]);
    }

    function baja($conFecha = true)
    {
        return $this->state([
            "ESTADO_EMP" => $this->faker->randomElement([2,3]),
            "FECHA_BAJA_EMP" => $conFecha === false ? null : CarbonImmutable::make($this->faker->date())
        ]);
    }
}
