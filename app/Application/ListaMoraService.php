<?php

namespace App\Application;

use App\Models\EmpleadorRepository;
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
    $ids = $items->pluck("id");

    $empleadorRepository = new EmpleadorRepository();
    $empleadores = $empleadorRepository->buscarPorIds($ids->all());
    
    return [$total, $items->map(function($item) use($empleadores){
      $empleador = $empleadores->where("id", $item->empleador_id)->first();
      return [
        "id" => $item->id,
        "numero_patronal" => $empleador["numero_patronal"],
        "nombre" => $empleador["nombre"],
        "empleado_id" => $empleador["id"]
      ];
    })];
  }

  function agregar($empleador_id){
    $empleadorRepository = new EmpleadorRepository();
    $empleador = $empleadorRepository->buscarPorId($empleador_id);
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