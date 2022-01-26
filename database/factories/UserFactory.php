<?php

namespace Database\Factories;

use App\Models\Regional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\ValueObjects\CarnetIdentidad;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $ci = explode("-", $this->faker->unique()->regexify("([1-9][0-9]{7})-([1-9][A-Z]){0,1}"));
        return [
            // "id" => $this->faker->unique()->randomNumber(),
            "ci" => new CarnetIdentidad(intval($ci[0]), $ci[1]),
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            'nombre' => $this->faker->name,
            'regional_id' => $this->faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]),
            'estado' => 1,//$this->faker->randomElement([1,2]),
            'username' => $this->faker->unique()->userName,
            'password' => $this->faker->password, //'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function activo()
    {
        return $this->state([
            "estado" => 1
        ]);
    }

    public function bloqueado()
    {
        return $this->state([
            "estado" => 2
        ]);
    }

    public function superUser()
    {
        return $this->hasAttached(Role::where("name", "super user")->first());
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

    public function withPermissions($permissions)
    {    
        $rol = Role::factory()->create();
        $rol->syncPermissions($permissions);
        return $this->hasAttached($rol);
    }    
}
