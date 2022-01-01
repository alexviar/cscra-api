<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Provincia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadesTerritorialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Departamento::truncate();
        Provincia::truncate();
        Municipio::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Departamento::importar(__DIR__.DIRECTORY_SEPARATOR."csv".DIRECTORY_SEPARATOR."departamentos.csv");
        // Provincia::importar(__DIR__.DIRECTORY_SEPARATOR."csv".DIRECTORY_SEPARATOR."provincias.csv");
        // Municipio::importar(__DIR__.DIRECTORY_SEPARATOR."csv".DIRECTORY_SEPARATOR."municipios.csv");
        $csv = __DIR__.DIRECTORY_SEPARATOR."csv".DIRECTORY_SEPARATOR."municipios.csv";
        $filename = str_replace("\\", "\\\\", $csv);
        
        DB::select("LOAD DATA LOCAL INFILE '{$filename}' IGNORE INTO TABLE `departamentos` CHARACTER SET 'utf8' FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\r\\n' IGNORE 1 LINES (`departamentos`.`id`, `departamentos`.`nombre`, @skip, @skip, @skip, @skip)");
        DB::select("LOAD DATA LOCAL INFILE '{$filename}' IGNORE INTO TABLE `provincias` CHARACTER SET 'utf8' FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\r\\n' IGNORE 1 LINES (`departamento_id`, @skip, `provincias`.`id`, `provincias`.`nombre`, @skip, @skip)");
        DB::select("LOAD DATA LOCAL INFILE '{$filename}' IGNORE INTO TABLE `municipios` CHARACTER SET 'utf8' FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\r\\n' IGNORE 1 LINES (@skip, @skip, `provincia_id`, @skip, `municipios`.`id`, `municipios`.`nombre`)");
    }
}
