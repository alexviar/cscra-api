<?php

namespace Database\Factories;

use App\Models\Galeno\Afiliado;
use App\Models\Medico;
use App\Models\Proveedor;
use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use Closure;
use Database\Factories\Galeno\AfiliacionTitularFactory;
use Database\Factories\Galeno\AfiliadoFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

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
    public function definition($attributes)
    {
        $regionalId = $attributes["regional_id"] ?? $this->faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]);        

        if(Arr::has($attributes, "paciente_id")){
            $pacienteId = $attributes["paciente_id"];
            $paciente = Afiliado::find($pacienteId instanceof Closure ? $pacienteId() : $pacienteId);
        }
        else{
            $paciente = (rand(0,1) ? Afiliado::factory()->titular() : Afiliado::factory()->beneficiario())->create();
        }

        $definition = [
            "fecha" => $attributes["fecha"] ?? new Carbon($this->faker->dateTime()),
            "prestacion" => $attributes["prestacion"] ?? $this->faker->text(100),
            "regional_id" => $regionalId,
            "medico_id" => $attributes["medico_id"] ?? Medico::factory(["regional_id"=>$regionalId]),
            "proveedor_id" => $attributes["proveedor_id"] ?? Proveedor::factory(["regional_id"=>$regionalId]),
            "paciente_id" => $paciente->id,
            "titular_id" => $paciente->titular ? $paciente->titular->id : null,
            "empleador_id" => $paciente->empleador->id,
            "user_id" => $attributes["user_id"] ?? User::factory()
        ];
        return $definition;
    }
    
    protected function getRawAttributes(?Model $parent)
    {
        return $this->definition($this->states->pipe(function ($states) {
            return $this->for->isEmpty() ? $states : new Collection(array_merge([function () {
                return $this->parentResolvers();
            }], $states->all()));
        })->reduce(function ($carry, $state) use ($parent) {
            if ($state instanceof Closure) {
                $state = $state->bindTo($this);
            }

            return array_merge($carry, $state($carry, $parent));
        }, []));
    }

    public function init($relations=[])
    {
        // $instance = $this->state(function($attributes){
        //     $medico = $relations["medico"] ?? Medico::factory()->create();
    
        //     $proveedor = $relations["proveedor"] ?? Proveedor::factory()->tipoRandom()->create();
        //     return [
        //         "medico_id" => $medico->id,
        //         "proveedor_id" => $proveedor->id
        //     ];
        // });
        // $afiliado = $relations["paciente"] ?? (rand(0, 1) ? Afiliado::factory()->titular()->create() : Afiliado::factory()->beneficiario()->create());
        

        // $login = $relations["user"] ?? User::factory()->create();

        // $instance = $this->for($afiliado, "paciente")
        //             ->for($afiliado->empleador)
        //             ->for($login, "usuario");
        // return $afiliado->tipo == 2 ? $instance->for($afiliado->titular, "titular") : $instance;
        return $this;
    }

    // public function forPaciente(Afiliado $paciente){
    //     return $this->state([
    //         "paciente_id" => $paciente->id,
    //         "titular_id" => $paciente->titular ? $paciente->titular->id : null,
    //         "empleador_id" => $paciente->empleador->id
    //     ]);
    // }

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
