<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Proveedor extends Model {
  // use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
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

  function contrato(){
    $today = Carbon::today(config("app.timezone"))->toDateString();
    return $this->hasOne(ContratoProveedor::class, "proveedor_id", "id")
      ->whereDate("fin", ">=", $today)
      ->whereDate("inicio", "<=", $today);
  }

  function historialContratos(){
    return $this->hasMany(ContratoProveedor::class, "proveedor_id", "id");
  }

  // function toArray()
  // {
  //   $array = parent::toArray();
  //   $array["ubicacion"] = ["latitude"=>$array["ubicacion"]->getLat(), "longitude"=>$array["ubicacion"]->getLng()];
  //   return $array;
  // }

  static function buscarPorNombre($nombre){
    $query = static::query();

    // $query->limit(50);

    $query->with(["medico.especialidad", "contrato.prestaciones"]);

    $query->whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$nombre."*"])
    ->orWhereHas("medico", function($query) use($nombre){
      $query->whereRaw("MATCH(`nombres`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$nombre."*"]);
    });

    return $query->get();
  }
}