<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfiliacionBeneficiario extends Model
{
    use HasFactory;

    protected $connection = "galeno";

    protected $table = "AFBENEFICIARIOS";

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $casts = [
        "FECHA_EXTINSION_BEN" => "date: d/m/Y",
        "fecha_extinsion" => "date: Y-m-d",
        "fecha_validez_seguro" => "date: Y-m-d",
    ];

    protected $hidden = [
        "CAUSAS_BEN",
        "FECHA_EXTINSION_BEN",
        "FECHA_INGRESO_BEN",
        "FECHA_I_BEN",
        // "ID",
        "IDSIRA",
        "ID_AFO",
        "ID_TTR",
        "OBSERVACIONES_BEN",
        "PARENTESCO_BEN",
        "REG_DATE",
        "REG_LOGIN"
    ];

    protected $appends = [
        "fecha_extinsion",
        "fecha_validez_seguro"
    ];

    function getEmpleadorAttribute()
    {
        return $this->afiliacionDelTitular ? $this->afiliacionDelTitular->empleador : null;
    }

    function getFechaExtinsionAttribute()
    {
        if($this->ampliacion){
            return $this->ampliacion->fecha_extinsion;
        }

        return $this->getAttribute("FECHA_EXTINSION_BEN") ?? null;
    }

    function getFechaValidezSeguroAttribute()
    {
        return $this->baja ? $this->baja->fecha_validez_seguro : null;
    }

    function afiliado()
    {
        return $this->belongsTo(Afiliado::class, "ID_AFO", "ID");
    }

    function baja()
    {
        return $this->hasOne(BajaAfiliacion::class, "ID_BNO", "ID");
    }

    function ampliacion()
    {
        return $this->hasOne(AmpliacionPrestacion::class, "ID_BNO", "ID");
    }

    function afiliacionDelTitular()
    {
        return $this->belongsTo(AfiliacionTitular::class, "ID_TTR", "ID");
    }
}
