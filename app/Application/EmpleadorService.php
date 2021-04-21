<?php

namespace App\Application;

use App\Models\Empleador;
use App\Models\EmpleadorRepository;
use Illuminate\Support\Arr;

class EmpleadorService {

  function buscar($filter, $page){
    $query = Empleador::query();
    if(Arr::has($filter, "numero_patronal")){
      $query->where("numero_patronal", $filter["numero_patronal"]);
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

    return [$total, $records];
  }

  function buscarPorPatronal($patronal){
    return Empleador::buscarPorNumeroPatronal($patronal);
  }
}