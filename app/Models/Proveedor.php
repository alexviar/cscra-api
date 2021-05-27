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
        "medico_id",
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

    protected $appends = ["tipo"];

    function getTipoAttribute()
    {
        return $this->tipo_id == 1 ? "Médico" : "Empresa";
    }

    function medico()
    {
        return $this->belongsTo(Medico::class, "medico_id", "id");
    }

    function contrato()
    {
        $today = Carbon::today(config("app.timezone"))->toDateString();
        return $this->hasOne(ContratoProveedor::class, "proveedor_id", "id")
            ->where("estado", 1)->where(function ($query) use ($today) {
                $query->whereNull("fin")
                    ->orWhere(function ($query) use ($today) {
                        $query->whereDate("fin", ">=", $today);
                    });
            })
            ->whereDate("inicio", "<=", $today);
    }

    function contratos()
    {
        return $this->hasMany(ContratoProveedor::class, "proveedor_id", "id");
    }

    function ofrece($prestacionId)
    {
        return $this->contrato->prestaciones->contains(fn ($prestacion) => $prestacion->id == $prestacionId);
    }

    // function toArray()
    // {
    //   $array = parent::toArray();
    //   $array["ubicacion"] = ["latitude"=>$array["ubicacion"]->getLat(), "longitude"=>$array["ubicacion"]->getLng()];
    //   return $array;
    // }

    static function buscarPorNombre($nombre)
    {
        $query = static::query();

        // $query->limit(50);

        $query->with(["medico.especialidad", "contrato.prestaciones"]);

        $query->whereHas("contrato");

        $query->whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$nombre . "*"])
            ->orWhereHas("medico", function ($query) use ($nombre) {
                $query->whereRaw("MATCH(`nombres`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$nombre . "*"]);
            });

        return $query->get();
    }
}
