<?php

namespace App\Models\Galeno;

use App\Models\Regional;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleador extends Model
{
    use HasFactory;

    protected $connection = "galeno";

    protected $table = "AFEMPLEADORES";

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $casts = [
        "FECHA_BAJA_EMP" => "date"
    ];

    protected $hidden = [
        "ANO_FUNDEMPRESA_EMP",
        "CASILLA_EMP",
        "CODIGO_DEP",
        "CODIGO_FUNDEMPRESA_EMP",
        "CODIGO_MUN",
        "CODIGO_PROV",
        "DEPARTAMENTO_EMP",
        "DIRECCION_EMP",
        "ESTADO_EMP",
        "FECHA_BAJA_EMP",
        "FECHA_CONSTITUCION_EMP",
        "FECHA_INGRESO_EMP",
        "FECHA_INICIACION_ACTIVIDADES_EMP",
        "FECHA_REG_EMP",
        "ID",
        "ID_RAD",
        "ID_RGL",
        "ID_TAO",
        "ID_USO",
        "LOCALIDAD_EMP",
        "MAIL_EMP",
        "NOMBRE_EMP",
        "NUMERO_EMP",
        "NUMERO_NIT_EMP",
        "NUMERO_PATRONAL_EMP",
        "OBSERVACIONES_EMP",
        "REG_DATE",
        "REG_LOGIN",
        "REPRESENTANTE_LEGAL_EMP",
        "TELEFONO_EMP",
        "TELEFONO_REPRESENTANTE_EMP",
        "TIPO_EMP",
        "WEB_EMP",
        "ZONA_EMP"
    ];

    protected $appends = [
        "id",
        "numero_patronal",
        "nombre",
        "estado",
        "estadoText",
        "fecha_baja"
    ];

    function getIdAttribute()
    {
        return $this->attributes["ID"];
    }

    function getNumeroPatronalAttribute()
    {
        return $this->getAttribute("NUMERO_PATRONAL_EMP");
    }

    function getNombreAttribute()
    {
        return $this->getAttribute("NOMBRE_EMP");
    }

    function getEstadoAttribute()
    {
        return $this->getAttribute("ESTADO_EMP");
    }

    function getEstadoTextAttribute()
    {
        switch($this->getAttribute("ESTADO_EMP")){
            case 1: return "Activo";
            case 2: return "Baja";
            case 3: return "Baja temporal";
            default: return "";
        }
    }

    function getFechaBajaAttribute()
    {
        return $this->getAttribute("FECHA_BAJA_EMP");
    }

    function getRegionalIdAttribute()
    {
        return $this->getAttribute("ID_RGL");
    }

    function getRegionalLocalIdAttribute()
    {
        return Regional::mapGalenoIdToLocalId($this->getAttribute("ID_RGL"));
    }

    function toArray()
    {
        $array = parent::toArray();
        $array["fecha_baja"] = $this->fecha_baja ? $this->fecha_baja->format("Y-m-d") : null;
        return $array;
    }

    static function buscarPorIds($ids)
    {
        return static::whereIn("ID", $ids)->get();
    }

    static function buscarPorId($id)
    {
        return static::where("ID", $id)->first();
    }

    static function buscarPorPatronal($numeroPatronal)
    {
        return static::where("NUMERO_PATRONAL_EMP", $numeroPatronal)->first();
    }
}
