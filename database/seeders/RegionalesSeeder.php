<?php

namespace Database\Seeders;

use App\Models\Regional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Regional::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Regional::create([
            "id" => 1,
            "nombre" => "La Paz",
            "departamento_id" => 2
        ]);
        Regional::create([
            "id" => 2,
            "nombre" => "Cochabamba",
            "departamento_id" => 3
        ]);
        Regional::create([
            "id" => 3,
            "nombre" => "Santa Cruz",
            "departamento_id" => 7
        ]);
        Regional::create([
            "id" => 4,
            "nombre" => "Oruro",
            "departamento_id" => 4
        ]);
        Regional::create([
            "id" => 5,
            "nombre" => "Potosí",
            "departamento_id" => 5
        ]);
        Regional::create([
            "id" => 6,
            "nombre" => "Sucre",
            "departamento_id" => 1
        ]);        
        Regional::create([
            "id" => 7,
            "nombre" => "Tarija",
            "departamento_id" => 6
        ]);
        Regional::create([
            "id" => 8,
            "nombre" => "Trinidad",
            "departamento_id" => 8
        ]);
        Regional::create([
            "id" => 9,
            "nombre" => "Cobija",
            "departamento_id" => 9
        ]);
        
        Regional::create([
            "id" => 10,
            "nombre" => "Tupiza",
            "departamento_id" => 5
        ]);
        Regional::create([
            "id" => 11,
            "nombre" => "Riberalta",
            "departamento_id" => 8
        ]);
    }
}
