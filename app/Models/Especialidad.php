<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Especialidad extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = "especialidades";

    protected $fillable = [
        "nombre"
    ];

    public static function importar($filename, $separador, $formatoSaltoLinea = "UNIX")
    {
        $saltoLinea = $formatoSaltoLinea == "DOS" ? "\\r\\n" : "\\n";
        $filename = str_replace("\\", "\\\\", $filename);
        DB::select("LOAD DATA LOCAL INFILE '{$filename}' INTO TABLE especialidades CHARACTER SET 'utf8' FIELDS TERMINATED BY '{$separador}' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '{$saltoLinea}' IGNORE 1 LINES (`especialidades`.`nombre`) SET `especialidades`.`id`=NULL");
        // DB::select("LOAD DATA LOCAL INFILE '{$filename}' INTO TABLE especialidades FIELDS TERMINATED BY '{$separador}' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '{$saltoLinea}' IGNORE 1 LINES (`especialidades`.`nombre`) SET `especialidades`.`id`=NULL");
    }
}
