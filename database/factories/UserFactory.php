<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $ci = explode("-", $this->faker->unique()->regexify("[0-9]{7,8}-[A-Z][0-9]"));
        return [
            "id" => $this->faker->unique()->randomNumber(),
            "ci_raiz" => $ci[0],
            "ci_complemento" => $ci[1],
            "apellido_paterno" => $this->faker->lastName,
            "apellido_materno" => $this->faker->lastName,
            'nombres' => $this->faker->name,
            'username' => $this->faker->unique()->userName,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Define the model's unverified state.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
