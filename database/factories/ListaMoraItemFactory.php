<?php

namespace Database\Factories;

use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Regional;
use Closure;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

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
    public function definition($attributes)
    {
        if(Arr::has($attributes, "empleador_id")){
            $id = $attributes["empleador_id"];
            $empleador = Empleador::find($id instanceof Closure ? $id() : $id);
        }
        else{
            $regional_id = $attributes["regional_id"] ?? $this->faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]);
            $empleador = Empleador::factory(["ID_RGL" => Regional::mapLocalIdToGalenoId($regional_id)])->create();
        }
        return [
            "empleador_id" => $empleador->id,
            "numero_patronal" => $empleador->numero_patronal,
            "nombre" => $empleador->nombre,
            "regional_id" => $empleador->regional_id
        ];
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
}
