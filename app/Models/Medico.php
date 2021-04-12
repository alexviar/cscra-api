<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model {
  public $timestamps = false;
  protected $table = "medicos";

  public function getNombreCompletoAttribute(){
    return trim("{$this->apellido_paterno} {$this->apellido_materno} {$this->nombres}");
  }

  public function especialidad(){
    return $this->belongsTo(Especialidad::class, "especialidad_id");
  }
}