<?php

namespace App\Models;

use App\Casts\CarnetIdentidad;
use App\Models\Traits\SaveToUpper;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    protected $appends = ["razon_social"];

    protected $hidden = ["ci_complemento"];

    protected $casts = ["ci" => CarnetIdentidad::class, "created_at" => "date:d/m/Y", "updated_at" => "date:d/m/Y"];

    function getPaddedIdAttribute(){        
        $id = str_pad($this->id, 20, '0', STR_PAD_LEFT);
        return $this->tipo == 1 ? "MED$id" : ($this->tipo == 2 ? "EMP$id" : null);
    }

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

    function getRazonSocialAttribute()
    {
        return $this->tipo == 1 ? $this->nombre_completo : $this->nombre;
    }

    function regional()
    {
        return $this->belongsTo(Regional::class, "regional_id", "id");
    }

    function toArray()
    {
        if($this->tipo == 2){
            $this->makeHidden("ci", "apellido_paterno", "apellido_materno", "especialidad");
        }
        $array = parent::toArray();
        $array["ubicacion"] = [
            "latitud" => $this->ubicacion->getLat(),
            "longitud" => $this->ubicacion->getLng()
        ];
        $array["id"] = $this->padded_id;
        return $array;
    }

    static function findById($id) {
        if(is_numeric($id)){
            return self::find($id);
        }
        $tipo = Str::substr($id, 0, 3);
        $tipo = $tipo === "MED" ? 1 : ($tipo === "EMP" ? 2 : 0);
        $id = Str::substr($id, 3);
        return self::where("tipo", $tipo)->where("id", $id)->first();
    }
}
