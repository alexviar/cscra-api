<?php

namespace App\Application;

use App\Models\Asegurado;
use App\Models\Empleador;
use App\Models\ListaMoraItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AseguradoService {

  function buscar($filter, $page, $include){
    $query = Asegurado::query();
    if(Arr::has($filter, "matricula")){
      $query->where("matricula", "like", Str::upper($filter["matricula"])."%");
    }
    if(Arr::has($filter, "id")){
      $query->where("id", $filter["id"]);
    }
    else if(Arr::has($filter, "ids")){
      $query->whereIn("id", $filter["ids"]);
    }

    $total = $query->count();

    $pageSize = Arr::get($page, "size", null);
    if($pageSize){
      $query->limit($pageSize);
    }
    if(Arr::has($page, "current")){
      $query->offset($page["current"], $pageSize);
    }

    $records = $query->get();

    $empleadores=null;
    $enMora=null;
    if(in_array("empleador", $include)){
      $idEmpleadores = $records->pluck("empleador_id");
      $empleadores = Empleador::buscarPorIds($idEmpleadores);
      $enMora = ListaMoraItem::buscarPorIdEmpleadores($idEmpleadores);
    }
    $titulares=null;
    if(in_array("titular", $include)){
      $idTitulares = $records->pluck("titular_id");
      $titulares = Asegurado::buscarPorIds($idTitulares);
    }
    $records = $records->map(function($asegurado) use($empleadores, $titulares, $enMora){
      $array = $asegurado->toArray();
      
      if($empleadores){
        $empleador = $empleadores->where("id", $asegurado->empleador_id)->first();
        $aportes = $enMora->contains(function ($item) use($asegurado){
          return $item->empleador_id == $asegurado->empleador_id;
        }) ? 0 : 1;
        $array["empleador"] = $empleador->toArray();
        $array["empleador"]["aportes"] = $aportes;
      }
      if($titulares && $asegurado->titular_id){
        $titular = $titulares->where("id", $asegurado->titular_id)->first();
        $array["titular"] = $titular ? $titular->toArray() : null;
      }
        
      return $array;
    });

    return [$total, $records];
  }
}