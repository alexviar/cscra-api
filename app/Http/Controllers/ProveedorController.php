<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use App\Models\Prestacion;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProveedorController extends Controller {
  function buscar(Request $request) {
    $page = $request->page;
    $query = Proveedor::query();
    $query->with("medico.especialidad", "contrato.prestaciones");
    if(!$page){
      return response()->json($query->get());
    }
  }

  function buscarPorNombre(Request $request){
    // $prestaciones = $request->nombre ? Prestacion::where("nombre", "like", $request->nombre . "%" )->get() : [];
    $records = [];
    if($request->nombre){
      $records = Proveedor::buscarPorNombre($request->nombre);
    }
    return response()->json($records);
  }
}