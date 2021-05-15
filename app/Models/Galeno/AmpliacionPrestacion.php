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

  protected $casts = [
    "FECHA_EXTINSION_AMP"=>"date: d/m/Y",
    "fecha_extinsion"=>"date: Y-m-d",
    "fecha_validez_seguro"=>"date: Y-m-d",
  ];

  protected $hidden = [

  ];

  protected $appends = [
    "id",
    "fecha_extinsion"
  ];

  function getIdAttribute(){
    return $this->attributes["ID"];
  }
  
  function getFechaExtinsionAttribute(){
    return $this->getAttribute("FECHA_EXTINSION_AMP");
  }
}