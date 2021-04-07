<?php

namespace App\Http\Controllers;

use App\Application\EmpleadorService;
use App\Models\Empleador;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EmpleadorController extends Controller {
  function buscar(Request $request): JsonResponse{
    $filter = $request->filter;

    $empleadorService = new EmpleadorService();
    $empleador = $empleadorService->buscarPorPatronal($filter["numero_patronal"]);
    if($empleador == null){
      throw new ModelNotFoundException("Empleador no existe");
    }
    return response()->json($empleador);
  }
}