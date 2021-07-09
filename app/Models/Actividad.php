<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Actividad extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "actividades";

    protected $fillable = [
        "inicio",
        "fin",
        "indicadores",
        "nombre"
    ];

    protected $casts = [
        "inicio" => "date:Y-m-d",
        "fin" => "date:Y-m-d",
        "extension" => "date:Y-m-d"
    ];

    protected $with = [
        "historial"
    ];

    protected $appends = [
        "avance",
        "avance_esperado"
    ];

    function getAvanceAttribute() {
        $last = $this->historial->last();
        return $last ? $last->actual : 0;
    }

    function getAvanceEsperadoAttribute() {
        $inicio = $this->inicio->subDay();
        $fin = $this->fin;
        $this->hoy = Carbon::now();

        $avanceEsperado = $this->hoy->diffInDays($inicio)/$fin->diffInDays($inicio);
        $avanceEsperado = round(max(0, min(1, $avanceEsperado))*100, 2);
        return $avanceEsperado;
    }

    function actualizarAvance($avance, $observaciones, UploadedFile $informe) {
        $avanceEsperado = $this->avance_esperado;

        $avanceModel = new Avance();
        $avanceModel->fecha = $this->hoy;
        $avanceModel->actual = $avance;
        $avanceModel->esperado = $avanceEsperado;
        $avanceModel->observaciones = $observaciones;

        return DB::transaction(function() use($avanceModel, $informe){
            $this->historial()->save($avanceModel);

            $filename = str_pad($avanceModel->id, 10, "0", STR_PAD_LEFT);
            $informe->move(storage_path("app/seguimiento/informes"), $filename);

            return $avanceModel;
        });
    }

    function historial() {
        return $this->hasMany(Avance::class, "actividad_id", "id")->orderBy("fecha")->orderBy("id");
    }
}
