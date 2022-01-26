<?php

namespace Database\Factories;

use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Regional;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListaMoraItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ListaMoraItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $empleador = Empleador::factory()->create();
        return [
            "empleador_id" => $empleador->id,
            "numero_patronal" => $empleador->numero_patronal,
            "nombre" => $empleador->nombre,
            "regional_id" => $empleador->regional_id
        ];
    }

    public function regionalLaPaz(){
        $empleador = Empleador::factory()->regionalLaPaz()->create();
        return $this->state([
            "empleador_id" => $empleador->id,
            "numero_patronal" => $empleador->numero_patronal,
            "nombre" => $empleador->nombre,
            "regional_id" => $empleador->regional_id
        ]);
    }

    public function regionalSantaCruz(){
        $empleador = Empleador::factory()->regionalSantaCruz()->create();
        return $this->state([
            "empleador_id" => $empleador->id,
            "numero_patronal" => $empleador->numero_patronal,
            "nombre" => $empleador->nombre,
            "regional_id" => $empleador->regional_id
        ]);
    }
}
