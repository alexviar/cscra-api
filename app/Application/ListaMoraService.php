<?php

namespace App\Application;

use App\Models\Empleador;
use App\Models\ListaMoraItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class ListaMoraService {
  function buscar($filter, $pagination){
    $query = ListaMoraItem::query();

    if($filter){
      if(Arr::has($filter, "empleador_id")){
        $query->where("empleador_id", $filter["empleador_id"]);
      }
    }

    $total = $query->count();
    
    $pageSize =$pagination["size"]; 
    $currentPage = $pagination["current"];
    $query->limit($pageSize)->offset(($currentPage-1)*$pageSize);


    $items = $query->get();
    $empleadorIds = $items->pluck("empleador_id");

    $empleadores = Empleador::buscarPorIds($empleadorIds->all());
    
    return [$total, $items->reduce(function($carry, $item) use($empleadores){
      $empleador = $empleadores->where("id", $item->empleador_id)->first();
      if($empleador){
        $carry[] = [
          "id" => $item->id,
          "numero_patronal" => $empleador["numero_patronal"],
          "nombre" => $empleador["nombre"],
          "empleador_id" => $empleador["id"]
        ];
      }
      return $carry;
    }, [])];
  }

  function agregar($empleador_id){
    $empleador = Empleador::buscarPorId($empleador_id);
    if(!$empleador)
      throw new ModelNotFoundException("El empleador no existe");
    return ListaMoraItem::firstOrCreate([
      "empleador_id" => $empleador_id
    ]);
  }

  function quitar($empleador_id){
    ListaMoraItem::where("empleador_id", $empleador_id)->delete();
  }
}