<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Permission;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition($attributes)
    {
        return [
            // "id" => $this->faker->unique()->randomNumber(),
            "name" =>  $attributes["name"] ?? $this->faker->unique()->text(32),
            "description" =>  $attributes["description"] ??  $this->faker->optional()->text(255),
            "guard_name" => "sanctum"
        ];
    }

    public function withRandomPermissions()
    {
        // $permisos = Permisos::toArray();
        // $granted = $this->faker->randomElements($permisos, $this->faker->numberBetween(1, count($permisos));
        $count = $this->faker->numberBetween(1, count(Permission::count()));
        $granted = Permission::random()->limit($count)->get();
        return $this->hasAttached($granted);
    }

    public function withPermissions($permisos)
    {
        return $this->hasAttached(Permission::whereIn("name", $permisos)->get());
    }
}
