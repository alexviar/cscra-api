<?php

namespace Database\Factories;

use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SolicitudAtencionExternaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SolicitudAtencionExterna::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "fecha" => $this->faker->dateTime(),
            "regional_id" => 1,
            "medico" => $this->faker->name,
            "proveedor" => $this->faker->name,
            "especialidad" => $this->faker->text(50)
        ];
    }

    public function regionalSantaCruz(){
        return $this->state([
            "regional_id" => 3
        ]);
    }

    public function registradoPor(User $user){
        return $this->for($user);
    }
}
