<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model {
  public $timestamps = false;
  protected $table = "medicos";

  public $with = ["especialidad"];

  protected $appends = ["especialidad", "nombreCompleto"];

  protected $fillable = [
    "ci",
    "ci_complemento",
    "apellido_paterno",
    "apellido_materno",
    "nombres",
    "regional_id",
    "especialidad_id",
    "es_proveedor"
  ];


  function getNombreCompletoAttribute(){
    $nombreCompleto = $this->nombres;
    if($this->apellido_materno){
      $nombreCompleto = $this->apellido_materno . " " . $nombreCompleto;
    }
    if($this->apellido_paterno){
      $nombreCompleto = $this->apellido_paterno . " " . $nombreCompleto;
    }
    return $nombreCompleto;
  }

  public function especialidad(){
    return $this->belongsTo(Especialidad::class, "especialidad_id");
  }

  public function getEspecialidadAttribute(){
    if(!$this->relationLoaded("especialidad")) $this->load("especialidad");
    $especialidad = $this->getRelation("especialidad");
    return $especialidad->nombre;
  }

  public function toArray(){
    $array = parent::toArray();
    $array = array_merge($array, ["especialidad"=>$this->especialidad, "ci" => [
      "raiz" => $array["ci"],
      "complemento" => $array["ci_complemento"]
    ]]);
    unset($array["ci_complemento"]);
    return $array;
  }
}