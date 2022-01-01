<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            RolesAndPermissionsSeeder::class,
            UnidadesTerritorialesSeeder::class,
            RegionalesSeeder::class,
        ]);

        // DB::table("tipos_proveedor")->insert([
        //     "id" => 1,
        //     "nombre" => "Médico"
        // ]);
        // DB::table("tipos_proveedor")->insert([
        //     "id" => 2,
        //     "nombre" => "Empresa"
        // ]);
    }
}
