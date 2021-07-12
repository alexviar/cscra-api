<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Area::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $id = 1;
        Area::create([
            "id" => $id++,
            "nombre" => "Afiliación"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Admisión y Fichaje"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Vigencia de derechos"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Control de empresas"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Contabilidad"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Adquisiciones"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Almacén"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Servicios generales"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Sistemas"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Bioestadística"
        ]);


        Area::create([
            "id" => $id++,
            "nombre" => "Información"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Emergencias"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Enfermería"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Anestesiología"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Terapia intensiva"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Laboratorio"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Imagenología"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Farmacia"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Nutrición"
        ]);
        Area::create([
            "id" => $id++,
            "nombre" => "Fisioterapia"
        ]);
    }
}
