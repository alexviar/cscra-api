<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use App\Models\Prestacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PrestacionController extends Controller {
  function buscar(Request $request) {
    $filter = $request->filter;
    $page  = $request->page;

    $query = Prestacion::query();
    if(Arr::has($filter, "nombre")){
      $query->whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$filter["nombre"]."*"]);
    }

    if($page && Arr::has($page, "size")){
      $total = $query->count();
      $query->limit($page["size"]);
      if(Arr::has($page, "current")){
        $query->offset(($page["current"] - 1) * $page["size"]);
      }
      return response()->json($this->buildPaginatedResponseData($total, $query->get()));
    }
    if(Arr::has($page, "current")){
      $query->offset($page["current"]);
    }
    return response()->json($query->get());
  }

  function buscarPorNombre(Request $request){
    $prestaciones = $request->nombre ? Prestacion::whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$request->nombre."*"] )->get() : [];
    return response()->json($prestaciones);
  }

  function ver(Request $request, int $id){
    $prestacion = Prestacion::find($id);
    if($prestacion)
      return response()->json($prestacion);
    throw new ModelNotFoundException("Prestacion no encontrada");
  }

  function registrar(Request $request){
    $prestacionClass = Prestacion::class;
    $payload = $request->validate([
      "nombre" => "required|unique:{$prestacionClass}"
    ]);

    $prestacion = Prestacion::create($payload);
    return response()->json($prestacion);
  }

  function actualizar(Request $request, int $id){
    $prestacionClass = Prestacion::class;
    $payload = $request->validate([
      "nombre" => "required|unique:{$prestacionClass},nombre,{$id}"
    ]);

    $prestacion = Prestacion::find($id);
    if(!$prestacion)
      throw new ModelNotFoundException("Prestacion no encontrada");
    $prestacion->nombre = $payload["nombre"];
    $prestacion->save();
    return response()->json($prestacion);
  }

  function eliminar(Request $request, int $id){
    Prestacion::destroy($id);
    return response()->json();
  }

  function importar(Request $request){
    $payload = $request->validate([
      "archivo" => "required",
      "separador" => "nullable",
      "formato" => "nullable"
    ]);
    Prestacion::importar($payload["archivo"], $payload["separador"], $payload["formato"]);
    return response()->json();
  }
}