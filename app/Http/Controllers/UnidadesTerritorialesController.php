<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Provincia;
use Illuminate\Http\Request;

class UnidadesTerritorialesController extends Controller {
  function getDepartamentos(Request $request){
    $departamentos = Departamento::get();
    return response()->json($departamentos);
  }

  function getProvincias(Request $request){
    $departamentos = Provincia::get();
    return response()->json($departamentos);
  }

  function getMunicipios(Request $request){
    $departamentos = Municipio::get();
    return response()->json($departamentos);
  }
}