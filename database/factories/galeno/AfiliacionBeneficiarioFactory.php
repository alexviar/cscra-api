<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\AfiliacionBeneficiario;
use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class AfiliacionBeneficiarioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AfiliacionBeneficiario::class;

    function createDate($date){
        return $date ? CarbonImmutable::make($date) : null;
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition($attributes)
    {
        $fechaI = $attributes["FECHA_INGRESO_BEN"] ?? $this->faker->date();

        return [
            "ID" => $this->faker->unique()->numerify("AA#############"),
            "FECHA_INGRESO_BEN" => $fechaI,
            "REG_LOGIN" => substr($this->faker->username(), 0, 15),
            "REG_DATE" => $this->faker->dateTimeBetween($fechaI, "now"),
            "PARENTESCO_BEN" => $attributes["PARENTESCO_BEN"] ?? $this->faker->numberBetween(1, 7),
            "FECHA_EXTINSION_BEN" => $attributes["FECHA_EXTINSION_BEN"] ?? null, //Arr::has($attributes, "FECHA_EXTINSION_BEN") ? $attributes["FECHA_EXTINSION_BEN"] : CarbonImmutable::make($this->faker->optional()->date), //$this->createDate($this->faker->optional()->date),
            "ID_TTR" => $attributes["ID_TTR"] ?? AfiliacionTitular::factory(),
            "ID_AFO" => $attributes["ID_AFO"] ?? Afiliado::factory([
                "TIPO_AFI" => 2
            ])
        ];
    }

    // function init(){
    //     return $this->for(AfiliacionTitular::factory()->init(), "afiliacionDelTitular");
    // }

    public function derechohabiente(){
        return $this->state([
            "FECHA_EXTINSION_BEN" => null,
            "PARENTESCO_BEN" => 8
        ]);
    }

    public function noExtinguible(){
        return $this->state([
            "FECHA_EXTINSION_BEN" => null
        ]);
    }

    public function extinguible($fecha = null){
        return $this->state([
            "FECHA_EXTINSION_BEN" => CarbonImmutable::make($fecha ?? $this->faker->date)
        ]);
    }

    // public function fechaExtinsionVencidaAyer(){
    //     return $this->state([
    //         "FECHA_EXTINSION_BEN" => Carbon::now()->subDay()
    //     ]);
    // }

    // public function fechaExtinsionVencidaHoy(){
    //     return $this->state([
    //         "FECHA_EXTINSION_BEN" => Carbon::now()
    //     ]);
    // }

    // public function fechaExtinsionVencidaManiana(){
    //     return $this->state([
    //         "FECHA_EXTINSION_BEN" => Carbon::now()->addDay()
    //     ]);
    // }
}
