<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaMoraItem extends Model {
  public $timestamps = false;

  protected $table = "lista_mora";

  protected $fillable = ["empleador_id"];

  static function buscarPorIdEmpleadores($idEmpleadores){
    return static::whereIn("empleador_id", $idEmpleadores)->get();
  }
}