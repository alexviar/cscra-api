<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\Empleador;
use App\Models\Regional;
use Carbon\Carbon;
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
    public function definition()
    {
        $fechaIngreso = $this->faker->date();
        return [
            "ID" => $this->faker->unique()->numerify("AA#############"),
            "NUMERO_PATRONAL_EMP" => $this->faker->unique()->numerify("###-#####"),
            "NOMBRE_EMP" => $this->faker->text(50),
            "ESTADO_EMP" => 1,
            "ID_RAD" => $this->faker->unique()->numerify("AA#############"),
            "ID_RGL" => $this->faker->randomElement(Regional::LOCAL_ID_TO_GALENO_ID),
            "FECHA_INGRESO_EMP" => $this->faker->date(),
            "REG_DATE" => $this->faker->dateTimeBetween($fechaIngreso, "now"),
            "REG_LOGIN" => substr($this->faker->username(), 0, 15),
            "ID_TAO" => $this->faker->unique()->numerify("AA#############")
        ];
    }

    function altaConFechaBaja()
    {
        return $this->state([
            "ESTADO_EMP" => 1,
            "FECHA_BAJA_EMP" => $this->faker->date()
        ]);
    }

    function estadoDesconocido()
    {
        return $this->state([
            "ESTADO_EMP" => 0,
        ]);
    }

    function bajaSinFecha()
    {
        return $this->state([
            "ESTADO_EMP" => 2,
            "FECHA_BAJA_EMP" => null
        ]);
    }

    function bajaHace2MesesMas1Dia()
    {
        return $this->state([
            "ESTADO_EMP" => 2,
            "FECHA_BAJA_EMP" => Carbon::now()
                ->subMonths(2)
                ->subDay(1)
        ]);
    }

    function bajaHace2Meses()
    {
        return $this->state([
            "ESTADO_EMP" => 2,
            "FECHA_BAJA_EMP" => Carbon::now()
                ->subMonths(2)
        ]);
    }

    function bajaHace2MesesMenos1Dia()
    {
        return $this->state([
            "ESTADO_EMP" => 2,
            "FECHA_BAJA_EMP" => Carbon::now()
                ->subMonths(2)
                ->addDay(1)
        ]);
    }
}
