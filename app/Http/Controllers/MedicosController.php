<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MedicosController extends Controller {
  function buscar(Request $request) {
    $query = Medico::query();
    $page = $request->page;
    $filter = $request->filter;

    $this->authorize("verTodo", [Medico::class, $filter]);
    
    if(Arr::has($filter, "nombre_completo") && $nombre=$filter["nombre_completo"]){
      $query->whereRaw("MATCH(`nombres`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$nombre."*"]);
    }
    if(Arr::has($filter, "tipo") && $tipo=$filter["tipo"]){
      $query->where("tipo", $tipo);
    }
    if($page && Arr::has($page, "size")){
      $total = $query->count();
      $query->limit($page["size"]);
      if(Arr::has($page, "current")){
        $query->offset(($page["current"] - 1) * $page["size"]);
      }
      $records = $query->get();
      $records->makeVisible("nombre_completo");
      return response()->json($this->buildPaginatedResponseData($total, $records));
    }
    if(Arr::has($page, "current")){
      $query->offset($page["current"]);
    }
    $records = $query->get();
    $records->makeVisible("nombre_completo");
    return response()->json($records);
  }

  function mostrar(Request $request, $id){
    $medico = Medico::find($id);
    $this->authorize("ver", $medico);
    if(!$medico){
      throw new ModelNotFoundException("Medico no existe");
    }
    return response()->json($medico);
  }
  
  function registrar(Request $request){
    $payload = $request->validate([
      "ci" => "required|numeric",
      "ci_complemento" => "nullable",
      "apellido_paterno" => "nullable",
      "apellido_materno" => "required",
      "nombres" => "required",
      "regional_id" => "required|numeric",
      "especialidad_id" => "required|numeric"
    ]);

    $this->authorize("registrar", [Medico::class, $payload]);

    $medico = Medico::create($payload);
    $medico->load("especialidad");
    return response()->json($medico);
  }

  function actualizar(Request $request, $id){

    $payload = $request->validate([
      "ci" => "required|numeric",
      "ci_complemento" => "nullable",
      "apellido_paterno" => "nullable",
      "apellido_materno" => "required",
      "nombres" => "required",
      "regional_id" => "required|numeric",
      "especialidad_id" => "required|numeric"
    ]);

    $medico = Medico::find($id);
    if(!$medico){
      throw new ModelNotFoundException("Medico no existe");
    }
    
    $this->authorize("actualizar", [$medico, $payload]);

    $medico->fill($payload);
    $medico->save();
    return response()->json($medico);
  }

  function cambiarEstado(Request $request, $id){
    $payload = $request->validate([
      "estado" => "required|numeric"
    ]);
    $medico = Medico::find($id);
    if(!$medico){
      throw new ModelNotFoundException("Medico no existe");
    }
    $this->authorize("cambiarEstado", $medico);
    
    $medico->update($payload);
    return response()->json($medico);
  }
}