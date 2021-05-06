<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Model;

class AfiliacionBeneficiario extends Model {

  protected $connection = "galeno";

  protected $table = "AFBENEFICIARIOS";

  protected $primaryKey = 'ID';
  
  public $incrementing = false;

  // In Laravel 6.0+ make sure to also set $keyType
  protected $keyType = 'string';

  protected $casts = [
    "FECHA_EXTINSION_BEN"=>"date: d/m/Y",
    "fecha_extinsion"=>"date: Y-m-d",
    "fecha_validez_seguro"=>"date: Y-m-d",
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

  function getEmpleadorAttribute(){
    return $this->afiliacionDelTitular?->empleador;
  }

  function getFechaExtinsionAttribute(){
    return $this->ampliacion?->fecha_extinsion ?: $this->getAttribute("FECHA_EXTINSION_BEN");
  }

  function getFechaValidezSeguroAttribute(){
    return $this->baja?->fecha_validez_seguro;
  }

  function baja(){
    return $this->hasOne(BajaAfiliacion::class, "ID_BNO", "ID");
  }

  function ampliacion(){
    return $this->hasOne(AmpliacionPrestacion::class, "ID_BNO", "ID");
  }

  function afiliacionDelTitular(){
    return $this->belongsTo(AfiliacionTitular::class, "ID_TTR", "ID");
  }
}