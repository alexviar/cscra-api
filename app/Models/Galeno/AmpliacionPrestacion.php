<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Model;

class AmpliacionPrestacion extends Model {

  protected $connection = "galeno";

  protected $table = "AFAMPLIACIONES_PRESTACIONES";

  protected $primaryKey = 'ID';
  
  public $incrementing = false;

  // In Laravel 6.0+ make sure to also set $keyType
  protected $keyType = 'string';

  protected $hidden = [

  ];

  protected $appends = [
    "id",
    "fecha_extinsion",
    "nombre",
    "estado",
    "fecha_baja"
  ];

  function getIdAttribute(){
    return $this->attributes["ID"];
  }
  
  function getFechaExtinsionAttribute(){
    return $this->attributes["FECHA_EXTINSION_AMP"];
  }
}