<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model {
  public $timestamps = false;
  protected $table = "medicos";

  public $with = ["especialidad"];

  protected $appends = ["especialidad"];

  public function getNombreCompletoAttribute(){
    return trim("{$this->apellido_paterno} {$this->apellido_materno} {$this->nombres}");
  }

  public function especialidad(){
    return $this->belongsTo(Especialidad::class, "especialidad_id");
  }

  public function getEspecialidadAttribute(){
    $especialidad = $this->getRelation("especialidad");
    return $especialidad->nombre;
  }

  public function toArray(){
    $array = parent::toArray();
    return array_merge($array, ["especialidad"=>$this->especialidad]);
  }
}