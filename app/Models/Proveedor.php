<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model {
  use SpatialTrait;
  public $timestamps = false;
  protected $table = "proveedores";

  protected $spatialFields = [
    'ubicacion',
  ];

  function toArray()
  {
    $array = parent::toArray();
    $array["ubicacion"] = ["latitude"=>$array["ubicacion"]->getLat(), "longitude"=>$array["ubicacion"]->getLng()];
    return $array;
  }
}