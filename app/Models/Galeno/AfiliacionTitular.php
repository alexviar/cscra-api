<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfiliacionTitular extends Model {
  use HasFactory;

  protected $connection = "galeno";

  protected $table = "AFTITULARES";
  
  protected $primaryKey = 'ID';
  
  public $incrementing = false;

  public $timestamps = false;
  
  // In Laravel 6.0+ make sure to also set $keyType
  protected $keyType = 'string';
  
  protected $casts = [
    "fecha_validez_seguro"=>"date:Y-m-d"
  ];

  protected $hidden = [
    "ESTADO",
    "FECHA_I_CSC_TIT",
    "FECHA_I_REG_TIT",
    "FECHA_I_TRABAJO_TIT",
    // "ID",
    "IDSIRA",
    "ID_AFO",
    "ID_EPR",
    "ID_TAO",
    "NUM_AF1_TIT",
    "NUM_RESOLUCION_TIT",
    "REG_DATE",
    "REG_LOGIN",
    "SALARIO_TIT",
  ];

  protected $appends = [
    "estado",
    "fecha_validez_seguro"
  ];

  function getEstadoAttribute(){
    return $this->baja ? 2 : 1;
  }

  function getFechaValidezSeguroAttribute(){
    return $this->baja?->fecha_validez_seguro;
  }

  function baja(){
    return $this->hasOne(BajaAfiliacion::class, "ID_TTR", "ID");
  }

  function empleador(){
    return $this->belongsTo(Empleador::class, "ID_EPR", "ID");
  }

  function afiliado(){
    return $this->belongsTo(Afiliado::class, "ID_AFO", "ID");
  }
}