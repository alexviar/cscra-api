<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string numeroPatronal
 * @property string nombre
 */
class Empleador extends Model {
  
  protected $table = "empleadores";

  public $incrementing = false;

  protected $keyType =  "string";

  protected $casts = [
    "fecha_baja" => "date:d/m/Y",
  ];

  static function buscarPorId($id){
    return static::where("id", $id)->first();
  }

  static function buscarPorIds($ids){
    return static::whereIn("id", $ids)->get();
  }
  
  static function buscarPorNumeroPatronal($numeroPatronal){
    return static::where("numero_patronal", $numeroPatronal)->first();
  }

}