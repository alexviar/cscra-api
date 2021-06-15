<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Proveedor extends Model
{
    use HasFactory;
    // use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
    use SpatialTrait;

    const EMPLEADO = 1;
    const EMPRESA = 2;

    public $timestamps = false;
    protected $table = "proveedores";

    protected $fillable = [
        "tipo_id",
        "nit",
        "ci",
        "ci_complemento",
        "apellido_paterno",
        "apellido_materno",
        "nombres",
        "especialidad_id",
        "nombre",
        "municipio_id",
        "ubicacion",
        "direccion",
        "telefono1",
        "telefono2",
        "regional_id"
    ];

    protected $spatialFields = [
        'ubicacion',
    ];

    protected $with = ["especialidad", "regional", "municipio.provincia.departamento"];

    protected $appends = ["tipo", "nombre_completo", "ci_text"];

    function getTipoAttribute()
    {
        return $this->tipo_id == 1 ? "Médico" : "Empresa";
    }

    // function medico()
    // {
    //     return $this->belongsTo(Medico::class, "medico_id", "id");
    // }

    function getCiTextAttribute()
    {
        return $this->ci . ($this->ci_complemento ? "-" .  $this->ci_complemento :  "");
    }
    
    function getNombreCompletoAttribute()
    {
        $nombreCompleto = $this->nombres;
        if($this->apellido_materno){
          $nombreCompleto = $this->apellido_materno . " " . $nombreCompleto;
        }
        if($this->apellido_paterno){
          $nombreCompleto = $this->apellido_paterno . " " . $nombreCompleto;
        }
        return $nombreCompleto;
    }

    function especialidad()
    {
        return $this->belongsTo(Especialidad::class, "especialidad_id", "id");
    }

    function contrato()
    {
        $today = Carbon::today(config("app.timezone"))->toDateString();
        return $this->hasOne(ContratoProveedor::class, "proveedor_id", "id")
            ->where("estado", '<>', 3)
            ->where(function($query) use($today){
                $query->whereDate("extension", ">=", $today)
                    ->orWhereNull("extension")
                    ->where(function ($query) use ($today) {
                        $query->whereDate("fin", ">=", $today)
                            ->orWhereNull("fin")
                            ->where("estado", 1);
                    });
            })
            ->whereDate("inicio", "<=", $today)
            ->orderBy("inicio", "DESC");
    }

    function contratos()
    {
        return $this->hasMany(ContratoProveedor::class, "proveedor_id", "id")->orderBy("inicio", "DESC");
    }

    function ofrece($prestacionId)
    {
        return $this->contrato->prestaciones->contains(function ($prestacion) use($prestacionId) {
            return $prestacion->id == $prestacionId;
        });
    }

    function regional()
    {
        return $this->belongsTo(Regional::class, "regional_id", "id");
    }

    function municipio()
    {
        return $this->belongsTo(Municipio::class, "municipio_id", "id");
    }

    function toArray()
    {
      $array = parent::toArray();
      $array["ubicacion"] = $this->ubicacion ? ["latitud"=>$array["ubicacion"]->getLat(), "longitud"=>$array["ubicacion"]->getLng()] : null;
      return $array;
    }

    static function buscarPorNombre($nombre)
    {
        $query = static::query();

        $query->with(["medico.especialidad", "contrato.prestaciones"]);

        $query->whereHas("contrato");

        $query->whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$nombre . "*"])
            ->orWhereHas("medico", function ($query) use ($nombre) {
                $query->whereRaw("MATCH(`nombres`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$nombre . "*"]);
            });

        return $query->get();
    }
}
