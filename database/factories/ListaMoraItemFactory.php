<?php

namespace Database\Factories;

use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
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
        return [
            "regional_id" => 1
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
