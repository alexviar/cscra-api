<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BajaAfiliacion extends Model
{
    use HasFactory;
    
    protected $connection = "galeno";

    protected $table = "AFBAJAS";

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $casts = [
        "REG_DATE" => "date:d/m/Y",
        "FECHA_REG_BAJ" => "date:d/m/Y",
        "FECHA_VALIDEZ_SEGURO_BAJ" => "date:d/m/Y"
    ];

    protected $hidden = [
        "ESTADO",
        "FECHA_PRESENTACION_BAJ",
        "FECHA_REG_BAJ",
        "FECHA_TRAB_BAJ",
        "FECHA_VALIDEZ_SEGURO_BAJ",
        "ID",
        "IDSIRA",
        "ID_BNO",
        "ID_RGL",
        "ID_TTR",
        "MOTIVO_BAJ",
        "NUMERO_AF03_BAJ",
        "REG_DATE",
        "REG_LOGIN",
        "SALARIO_RETIRO_BAJ"
    ];

    protected $appends = [
        "fecha_validez_seguro",
        "fecha_registro"
    ];

    function afiliacionTitular(){
        return $this->belongsTo(AfiliacionTitular::class, "ID_TTR", "ID");
    }

    function afiliacionBeneficiario(){
        return $this->belongsTo(AfiliacionBeneficiario::class, "ID_BNO", "ID");
    }

    function getFechaValidezSeguroAttribute()
    {
        return $this->getAttribute("FECHA_VALIDEZ_SEGURO_BAJ");
    }

    function getFechaRegistroAttribute()
    {
        return ($this->getAttribute("FECHA_REG_BAJ") ?? $this->getAttribute("REG_DATE"))->format("d/m/Y");
    }
}
