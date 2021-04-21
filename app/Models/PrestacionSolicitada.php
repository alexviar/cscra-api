<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestacionSolicitada extends Model{
  public $timestamps = false;

  protected $table = "detalles_atenciones_externas";

  protected $fillable = ["prestacion_id", "nota"];

  protected $with = ["prestacion"];

  function getPrestacionAttribute(){
    $value = $this->getRelation("prestacion");
    return $value ? $value->nombre : null;
  }

  function prestacion(){
    return $this->belongsTo(Prestacion::class, "prestacion_id", "id");
  }
}