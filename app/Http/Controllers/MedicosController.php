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
    if(Arr::has($page, "size")){
      $query->limit($request->page["size"]);
    }
    $filter = $request->filter;
    if(Arr::has($filter, "nombre_completo")){
      $query->whereRaw("MATCH(`nombres`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$request->filter["nombre_completo"]."*"]);
    }

    return response()->json([
      "meta"=>[
        "total" => $query->count()
      ],
      "records" => $query->get()
    ]);
  }

  function mostrar(Request $request, $id){
    $medico = Medico::find($id);
    if(!$medico){
      throw new ModelNotFoundException("Medico no existe");
    }
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

    $medico->fill($payload);
    $medico->save();
    return response()->json($medico);
  }

  function eliminar(Request $request, $id){
    Medico::destroy($id);
    return response()->json();
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

    $medico = Medico::create($payload);
    $medico->load("especialidad");
    return response()->json($medico);
  }
}