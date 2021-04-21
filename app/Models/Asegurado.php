<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string matricula
 * @property string apellido_paterno
 * @property string apellido_materno
 * @property string nombres
 * @property string estado
 * @property string fecha_extinsion
 */
class Asegurado extends Model {

  public $incrementing = false;

  protected $keyType =  "string";

  protected $table = "asegurados";

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

  function getMatriculaAttribute($value){
    return explode("-", $value);
  }

  function toArray()
  {
    $array = parent::toArray();
    $array["matricula"] = $this->attributes["matricula"];
    return $array;
  }

  static function buscarPorIds($ids){
    return static::whereIn("id", $ids)->get();
  }

  static function buscarPorId($id){
    return static::where("id", $id)->first();
  }

  static function buscarPorMatricula($matricula){
    return static::where("matricula", "like", $matricula."%");
  }
}