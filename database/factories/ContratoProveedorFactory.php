<?php

namespace Database\Factories;

use App\Models\ContratoProveedor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContratoProveedorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContratoProveedor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "fin" => null,
            "estado" => 1
        ];
    }

    public function consumido(){
        return $this->state([
            "estado" => 2
        ]);
    }

    public function anulado(){
        return [
            "estado" => 3
        ];
    }

    public function inicioAyer(){
        return $this->state([
            "inicio" => Carbon::now()->subDay()
        ]);
    }

    public function iniciaHoy(){
        return $this->state([
            "inicio" => Carbon::now()
        ]);
    }
    
    public function iniciaManiana(){
        return $this->state([
            "inicio" => Carbon::now()->addDay()
        ]);
    }

    public function finalizoAyer(){
        return $this->state([
            "fin" => Carbon::now()->subDay()
        ]);
    }

    public function finalizaHoy(){
        return $this->state([
            "fin" => Carbon::now(),
        ]);
    }

    public function finalizaManiana(){
        return $this->state([
            "fin" => Carbon::now()->addDay()
        ]);
    }
}
