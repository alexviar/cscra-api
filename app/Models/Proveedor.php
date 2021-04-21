<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model {
  use SpatialTrait;

  const EMPLEADO = 1;
  const EMPRESA = 2;

  public $timestamps = false;
  protected $table = "proveedores";

  protected $spatialFields = [
    'ubicacion',
  ];

  function medico(){
    return $this->hasOne(Medico::class, "id", "medico_id");
  }



  // function toArray()
  // {
  //   $array = parent::toArray();
  //   $array["ubicacion"] = ["latitude"=>$array["ubicacion"]->getLat(), "longitude"=>$array["ubicacion"]->getLng()];
  //   return $array;
  // }

  static function buscarPorNombre($nombre){
    $query = static::query();

    $query->limit(50);

    $query->with(["medico", "medico.especialidad"]);

    $query->whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$nombre."*"])
    ->orWhereHas("medico", function($query) use($nombre){
      $query->whereRaw("MATCH(`nombres`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$nombre."*"]);
    })
    ;
    return $query->get();
  }
}