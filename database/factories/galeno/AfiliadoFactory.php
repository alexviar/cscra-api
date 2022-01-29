<?php

namespace Database\Factories\Galeno;

use App\Models\Galeno\AfiliacionBeneficiario;
use App\Models\Galeno\AfiliacionTitular;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use App\Models\Regional;
use Illuminate\Database\Eloquent\Factories\Factory;

class AfiliadoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Afiliado::class;

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
            "MATRICULA" => strtoupper($this->faker->bothify("##-####-???")),
            "MATRICULA_CO" => $this->faker->randomElement([0,1,2,3,4,5,6,7,8,9]),
            "PATERNO_AFI" => $this->faker->lastName,
            "MATERNO_AFI" => $this->faker->lastName,
            "NOMBRE_AFI" => $this->faker->name,
            "FECHA_NACIMIENTO_AFI" => $this->faker->date(),
            "FOJAS" => 0,
            "TIPO_AFI" => $attributes["TIPO_AFI"] ?? $this->faker->randomElement([1,2]),
            "ESTADO_AFI" => $attributes["ESTADO_AFI"] ?? 1,
            "ID_RGL" => $this->faker->randomElement(Regional::LOCAL_ID_TO_GALENO_ID),
            "SEXO_AFI" => $this->faker->randomElement([1,2]),
            "REG_DATE" => $this->faker->dateTimeBetween($fechaIngreso, "now"),
            "REG_LOGIN" => substr($this->faker->username(), 0, 15)
        ];
    }

    public function beneficiario($afiliacion=null){
        // $titular = $titular ?? Afiliado::factory()->titular()->create();
        if($afiliacion === null){
            $afiliacion = AfiliacionBeneficiario::factory();
        }
        else if($afiliacion === false){
            return $this->state([
                "TIPO_AFI" => 2
            ]);
        }
        return $this->has($afiliacion, "afiliacionesComoBeneficiario")->state([
            "TIPO_AFI" => 2
        ]);
    }

    public function titular($afiliacion=null){
        // $titular = $titular ?? Afiliado::factory()->titular()->create();
        if($afiliacion === null){
            $afiliacion = AfiliacionTitular::factory();
        }
        else if($afiliacion === false){
            return $this->state([
                "TIPO_AFI" => 1
            ]);
        }
        return $this->has($afiliacion, "afiliacionesComoTitular")->state([
            "TIPO_AFI" => 1
        ]);
    }

    public function baja(){
        return $this->state([
            "ESTADO_AFI" => 2
        ]);
    }

    public function estadoDesconocido(){
        return $this->state([
            "ESTADO_AFI" => $this->faker->randomDigitNot([1, 2])
        ]);
    }
}
