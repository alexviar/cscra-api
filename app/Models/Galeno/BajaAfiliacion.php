<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Model;

class BajaAfiliacion extends Model {

  protected $connection = "galeno";

  protected $table = "AFBAJAS";
  
  protected $primaryKey = 'ID';
  
  public $incrementing = false;
  
  // In Laravel 6.0+ make sure to also set $keyType
  protected $keyType = 'string';
  
  protected $casts = [
    "FECHA_VALIDEZ_SEGURO_BAJ"=>"date:d/m/Y"
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
    "fecha_validez_seguro"
  ];

  function getFechaValidezSeguroAttribute(){
    return $this->getAttribute("FECHA_VALIDEZ_SEGURO_BAJ");
  }
}