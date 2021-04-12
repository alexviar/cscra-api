<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestacionSolicitada extends Model{
  public $timestamps = false;

  protected $table = "detalles_atenciones_externas";
}