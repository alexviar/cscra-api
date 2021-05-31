<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model {
  use HasFactory;
  
  public $timestamps = false;
  protected $table = "medicos";

  public $with = ["especialidad"];

  protected $appends = ["especialidad", "nombre_completo", "ci_text", "estado_text"];

  protected $fillable = [
    "ci",
    "ci_complemento",
    "apellido_paterno",
    "apellido_materno",
    "nombres",
    "regional_id",
    "estado",
    "especialidad_id",
    "tipo"
  ];

  function getCiTextAttribute(){
    return $this->ci . ($this->ci_complemento ? "-" .  $this->ci_complemento :  "");
  }
  
  function getEstadoTextAttribute(){
    switch($this->estado){
      case 1: return "Alta";
      case 2: return "Baja";
    }
  }

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
      "complemento" => $array["ci_complemento"] ?? null
    ]]);
    unset($array["ci_complemento"]);
    return $array;
  }
}