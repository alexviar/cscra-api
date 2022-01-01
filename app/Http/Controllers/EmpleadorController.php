<?php

namespace App\Http\Controllers;

use App\Application\EmpleadorService;
use App\Models\Empleador;
use App\Models\Galeno\Empleador as GalenoEmpleador;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EmpleadorController extends Controller {
  
  function buscar(Request $request): JsonResponse{
    $filter = $request->filter;
    $page = $request->page;

    $query = GalenoEmpleador::query();
    if(Arr::has($filter, "numero_patronal")){
      $query->where("NUMERO_PATRONAL_EMP", $filter["numero_patronal"]);
    }
    if(Arr::has($filter, "id")){
      $query->where("ID", $filter["id"]);
    }
    else if(Arr::has($filter, "ids")){
      $query->whereIn("ID", $filter["ids"]);
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

    return  response()->json($this->buildPaginatedResponseData($total, $records));
  }

  function buscarPorPatronal(Request $request): JsonResponse {
    $empleador = GalenoEmpleador::where("NUMERO_PATRONAL_EMP", $request->numero_patronal)->first();//$this->service->buscarPorPatronal($request->numero_patronal);
    if($empleador)
      return response()->json($empleador);
    throw new ModelNotFoundException("Empleador no encontrado");
  }
}