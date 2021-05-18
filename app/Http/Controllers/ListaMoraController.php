<?php

namespace App\Http\Controllers;

use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Regional;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ListaMoraController extends Controller {

  function buscar(Request $request): JsonResponse{

    $query = ListaMoraItem::query();

    $page =  $request->page;
    $filter = $request->filter;

    $this->authorize("ver", [ListaMoraItem::class, $filter]);

    if(Arr::has($filter, "numero_patronal") && ($numero_patronal = $filter["numero_patronal"])){
      $query->where("numero_patronal", $numero_patronal);
    }
    else {
      if(Arr::has($filter, "regional_id") && ($regionalId = $filter["regional_id"])){
        $query->where("regional_id", $regionalId);
      }
      if(Arr::has($filter, "nombre") && ($nombre = $filter["nombre"])){
        $query->where("nombre", "like", "%$nombre%");
      }
    }

    if($page && Arr::has($page, "size")){
      $total = $query->count();
      $query->limit($page["size"]);
      if(Arr::has($page, "current")){
        $query->offset(($page["current"] - 1) * $page["size"]);
      }
      $records = $query->get();
      return response()->json($this->buildPaginatedResponseData($total, $records));
    }
    if(Arr::has($page, "current")){
      $query->offset($page["current"]);
    }

    $records = $query->get();
    return response()->json($records);
  }

  function agregar(Request $request): JsonResponse {
    $payload = $request->validate([
      "empleador_id" => "required"
    ]);
    $empleador = Empleador::buscarPorId($payload["empleador_id"]);

    $this->authorize("agregar", [ListaMoraItem::class, $empleador]);

    if(!$empleador)
      throw new ModelNotFoundException("El empleador no existe");
    if($item = ListaMoraItem::where("empleador_id", $payload["empleador_id"])->first()){
      throw ValidationException::withMessages([
        "empleador_id" => "El empleador ya fue agregado a la lista de mora"
      ]);//ConflictException::withData("El empleador ya fue agregado a la lista de mora", $item);
    }
    $item = ListaMoraItem::create([
      "empleador_id" => $payload["empleador_id"],
      "numero_patronal" => $empleador->numero_patronal,
      "nombre" => $empleador->nombre,
      "regional_id" => Regional::mapGalenoIdToLocalId($empleador->ID_RGL)
    ]);

    return response()->json($item);
  }

  function quitar(Request $request): JsonResponse {
    $payload = $request->validate([
      "empleador_id" => "required"
    ]);
    $item = ListaMoraItem::buscarPorIdEmpleador($payload["empleador_id"]);
    $this->authorize("agregar", $item);
    if(!$item)
      throw new ModelNotFoundException();
    $item->delete();
    return response()->json();
  }
}