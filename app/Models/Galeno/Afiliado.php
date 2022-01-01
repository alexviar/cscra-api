<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Afiliado extends Model
{
    use HasFactory;

    protected $connection = "galeno";

    protected $table = "AFAFILIADOS";

    public $timestamps = false;

    protected $casts = [
        "fecha_extinsion" => "date: Y-m-d",
        "fecha_validez_seguro" => "date: Y-m-d",
    ];

    protected $hidden = [
        "FOTO_AFI",
        "CARNET",
        "CARNET_EXP",
        "DEP_NACIMIENTO_AFI",
        "DIRECCION_AFI",
        "ESTADO_AFI",
        "ESTADO_CIVIL_AFI",
        "FECHA_NACIMIENTO_AFI",
        "FOJAS",
        "FOTO",
        "ID",
        "IDSIRA",
        "ID_RGL",
        "LUGAR_NACIMIENTO_AFI",
        "MATERNO_AFI",
        "MATRICULA",
        "MATRICULA_CO",
        "MOVIL_AFI",
        "NOMBRE_AFI",
        "NUM_AFILIADO_AFI",
        "OBSERVACIONES",
        "OCUPACION_AFI",
        "PATERNO_AFI",
        "REG_DATE",
        "REG_LOGIN",
        "SEXO_AFI",
        "TELEFONO_AFI",
        "TIPO_AFI",
        "TIPO_SANGRE_AFI",
        "afiliacionesComoTitular",
        "afiliacionesComoBeneficiario"
    ];

    protected $appends = [
        "id",
        "matricula",
        "matricula_complemento",
        "nombre_completo",
        "apellidoPaterno",
        "apellidoMaterno",
        "nombres",
        "estado",
        "estado_text",
        "tipo",
        "empleador",
        "afiliacion",
        "titular"
    ];

    protected $primaryKey = 'ID';

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    function getIdAttribute()
    {
        return $this->attributes["ID"];
    }

    function getMatriculaAttribute()
    {
        return $this->attributes["MATRICULA"]; // .  "-".$this->attributes["MATRICULA_CO"];
    }

    function getMatriculaComplementoAttribute()
    {
        return $this->getAttribute("MATRICULA_CO");
    }

    function getNombreCompletoAttribute()
    {
        $nombreCompleto = $this->nombres;
        if ($this->apellido_materno) {
            $nombreCompleto = $this->apellido_materno . " " . $nombreCompleto;
        }
        if ($this->apellido_paterno) {
            $nombreCompleto = $this->apellido_paterno . " " . $nombreCompleto;
        }
        return $nombreCompleto;
    }

    function getEstadoTextAttribute()
    {
        switch ($this->ESTADO_AFI) {
            case 1:
                return "Activo";
            case 2:
                return "Baja";
            default:
                return "";
        }
    }

    function getApellidoPaternoAttribute()
    {
        return $this->PATERNO_AFI;
    }

    function getApellidoMaternoAttribute()
    {
        return $this->MATERNO_AFI;
    }

    function getNombresAttribute()
    {
        return $this->NOMBRE_AFI;
    }

    function getEstadoAttribute()
    {
        return $this->ESTADO_AFI;
    }

    function getTipoAttribute()
    {
        return $this->TIPO_AFI;
    }

    function getEmpleadorAttribute()
    {
        return $this->ultimaAfiliacion ? $this->ultimaAfiliacion->empleador : null;
    }

    function getTitularAttribute()
    {
        return $this->afiliacionDelTitular ? $this->afiliacionDelTitular->afiliado : null;
    }

    function getAfiliacionDelTitularAttribute()
    {
        if ($this->tipo == 2) {
            return $this->ultimaAfiliacion ? $this->ultimaAfiliacion->afiliacionDelTitular : null;
        }
    }

    function getFechaExtincionAttribute()
    {
        if ($this->tipo == 2) {
            return $this->ultimaAfiliacion ? $this->ultimaAfiliacion->fecha_extincion : null;
        }
    }

    function getFechaValidezSeguroAttribute()
    {
        return ($this->ultimaAfiliacion && $this->ultimaAfiliacion->baja) ? $this->ultimaAfiliacion->baja->fecha_validez_seguro : null;
    }

    function getAfiliacionAttribute()
    {
        return $this->getUltimaAfiliacionAttribute();
    }

    function getUltimaAfiliacionAttribute()
    {
        if ($this->tipo == 1) {
            return $this->afiliacionesComoTitular->sortByDesc(function ($afi) {
                return $afi->ID;
            })->first();
        } else if ($this->tipo == 2) {
            return $this->afiliacionesComoBeneficiario->sortByDesc(function ($afi) {
                return $afi->ID;
            })->first();
        }
    }

    function afiliacionesComoTitular()
    {
        return $this->hasMany(AfiliacionTitular::class, "ID_AFO", "ID");
    }

    function afiliacionesComoBeneficiario()
    {
        return $this->hasMany(AfiliacionBeneficiario::class, "ID_AFO", "ID");
    }

    function toArray()
    {
        $array = parent::toArray();
        return $array;
    }

    static function buscarPorId($id)
    {
        return static::find($id);
    }

    static function buscarPorIds($ids)
    {
        return static::whereIn("ID", $ids)->get();
    }

    static function buscarPorMatricula($matricula)
    {
        return static::where("MATRICULA", $matricula)->get();
    }
}
