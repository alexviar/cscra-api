<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\BajaAfiliacion;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class BajaAfiliacionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BajaAfiliacion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition($attributes)
    {
        return [
            "ID" => $this->faker->unique()->numerify("AA#############"),
            "FECHA_PRESENTACION_BAJ" => $this->faker->date(),
            "FECHA_REG_BAJ" => $this->faker->date(),            
            "FECHA_VALIDEZ_SEGURO_BAJ" => Arr::has($attributes, "FECHA_VALIDEZ_SEGURO_BAJ") ? $attributes["FECHA_VALIDEZ_SEGURO_BAJ"] : $this->faker->date(),
            // "ID_BNO" => $attributes["ID_BNO"],
            // "ID_TTR" => $attributes["ID_TTR"],
            "ESTADO" => 1
        ] + $attributes;
    }

    public function sinVencimiento(){
        return $this->state([
            "FECHA_VALIDEZ_SEGURO_BAJ" => null
        ]);
    }

    public function vencimiento($fecha){
        return $this->state([
            "FECHA_VALIDEZ_SEGURO_BAJ" => CarbonImmutable::make($fecha)
        ]);
    }
}
