<?php

namespace App\Models;

use App\Casts\CarnetIdentidad;
use App\Models\Traits\SaveToUpper;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model {
  use HasFactory, SaveToUpper;
  
  protected $table = "medicos";

  protected $with = ["regional"];

  protected $appends = ["nombre_completo"];
  
  protected $hidden = ["ci_complemento"];

  protected $casts = [
    "ci" => CarnetIdentidad::class,
    "created_at" =>  'date:d/m/Y',
    "updated_at" =>  'date:d/m/Y'
  ];

  protected $fillable = [
    "ci",
    "ci_complemento",
    "apellido_paterno",
    "apellido_materno",
    "nombre",
    "regional_id",
    "estado",
    "especialidad"
  ];
  
  function getEstadoTextAttribute(){
    switch($this->estado){
      case 1: return "Alta";
      case 2: return "Baja";
    }
  }

  function getNombreCompletoAttribute(){
    $nombreCompleto = $this->nombre;
    if($this->apellido_materno){
      $nombreCompleto = $this->apellido_materno . " " . $nombreCompleto;
    }
    if($this->apellido_paterno){
      $nombreCompleto = $this->apellido_paterno . " " . $nombreCompleto;
    }
    return $nombreCompleto;
  }

  public function regional(){
    return $this->belongsTo(Regional::class, "regional_id");
  }
}