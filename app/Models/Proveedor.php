<?php

namespace App\Models;

use App\Casts\CarnetIdentidad;
use App\Models\Traits\SaveToUpper;
use Exception;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * @property Point $ubicacion
 */
class Proveedor extends Model
{
    use HasFactory, SaveToUpper, SpatialTrait;

    protected $table = "proveedores";

    protected $fillable = [
        "tipo",
        "estado",
        "nit",
        "ci",
        "ci_complemento",
        "apellido_paterno",
        "apellido_materno",
        "nombre",
        "especialidad",
        "direccion",
        "ubicacion",
        "telefono1",
        "telefono2",
        "regional_id"
    ];

    protected $spatialFields = [
        'ubicacion',
    ];

    protected $with = ["regional"];

    protected $appends = ["nombre_completo"];

    protected $hidden = ["ci_complemento"];

    protected $casts = ["ci" => CarnetIdentidad::class, "created_at" => "date:d/m/Y", "updated_at" => "date:d/m/Y"];

    function getNombreCompletoAttribute()
    {
        $nombreCompleto = $this->nombre;
        if ($this->apellido_materno) {
            $nombreCompleto = $this->apellido_materno . " " . $nombreCompleto;
        }
        if ($this->apellido_paterno) {
            $nombreCompleto = $this->apellido_paterno . " " . $nombreCompleto;
        }
        return $nombreCompleto;
    }

    function regional()
    {
        return $this->belongsTo(Regional::class, "regional_id", "id");
    }

    function toArray()
    {
        if($this->tipo == 2){
            $this->makeHidden("ci", "apellido_paterno", "apellido_materno", "nombre", "especialidad");
        }
        $array = parent::toArray();
        $array["ubicacion"] = [
            "latitud" => $this->ubicacion->getLat(),
            "longitud" => $this->ubicacion->getLng()
        ];
        return $array;
    }
}
