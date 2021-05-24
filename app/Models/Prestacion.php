<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Prestacion extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = "prestaciones_salud";

    protected $fillable = [
        "nombre"
    ];

    public static function importar($filename, $separador = ",", $formatoSaltoLinea = "UNIX")
    {
        $saltoLinea = $formatoSaltoLinea == "DOS" ? "\\r\\n" : "\\n";
        $filename = str_replace("\\", "\\\\", $filename);
        DB::select("LOAD DATA LOCAL INFILE '{$filename}' IGNORE INTO TABLE `prestaciones_salud` CHARACTER SET 'utf8' FIELDS TERMINATED BY '{$separador}' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '{$saltoLinea}' IGNORE 1 LINES (`prestaciones_salud`.`nombre`) SET `prestaciones_salud`.`id`=NULL");
        // DB::select("LOAD DATA LOCAL INFILE '{$filename}' INTO TABLE especialidades FIELDS TERMINATED BY '{$separador}' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '{$saltoLinea}' IGNORE 1 LINES (`especialidades`.`nombre`) SET `especialidades`.`id`=NULL");
    }
}
