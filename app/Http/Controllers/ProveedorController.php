<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use App\Models\Prestacion;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller {
  function buscar(Request $request) {
    $page = $request->page;
    $query = Proveedor::query();
    $query->with("medico.especialidad", "contrato.prestaciones");

    if($page && Arr::has($page, "size")){
      $query->limit($page["size"]);
      if(Arr::has($page, "current")){
        $query->offset(($page["current"] - 1) * $page["size"]);
      }
      $total = $query->count();
      return response()->json($this->buildPaginatedResponseData($total, $query->get()));
    }
    if(Arr::has($page, "current")){
      $query->offset($page["current"]);
    }
    return response()->json($query->get());
  }

  function buscarPorNombre(Request $request){
    // $prestaciones = $request->nombre ? Prestacion::where("nombre", "like", $request->nombre . "%" )->get() : [];
    $records = [];
    if($request->nombre){
      $records = Proveedor::buscarPorNombre($request->nombre);
    }
    return response()->json($records);
  }

  function registrar(Request $request){
    $tipo_id = $request->tipo_id;
    if($tipo_id == 1){
      $payload = $request->validate([
        "nit" => "nullable",
        "ci" => "required",
        "ci_complemento" => "nullable",
        "apellido_paterno" => "nullable",
        "apellido_materno" => "required",
        "nombres" => "required",
        "especialidad_id" => "required",
        "regional_id" => "required"
      ]);
      $proveedor = DB::transaction(function() use($payload){
        $medico = Medico::create([
          "ci" => $payload["ci"],
          "ci_complemento" => $payload["ci_complemento"],
          "apellido_paterno" => $payload["apellido_paterno"],
          "apellido_materno" => $payload["apellido_materno"],
          "nombres" => $payload["nombres"],
          "especialidad_id" => $payload["especialidad_id"],
          "regional_id" => $payload["regional_id"],
          "es_proveedor" => true
        ]);
        $proveedor = Proveedor::create([
          "tipo_id" => 1,
          "nit"=>$payload["nit"],
          "regional_id" => $payload["regional_id"],
          "medico_id" => $medico->id
        ]);
        $proveedor->load(["medico"]);
        return $proveedor;
      });
      return response()->json($proveedor);
    }
    else if($tipo_id == 2){
      $payload = $request->validate([
        "nit" => "nullable",
        "nombre" => "required",
        "regional_id" => "required"
      ]);
      $payload["tipo_id"] = 2;
      $proveedor = Proveedor::create($payload);
      return response()->json($proveedor);
    }
    else{
      abort(400);
    }
  }
}