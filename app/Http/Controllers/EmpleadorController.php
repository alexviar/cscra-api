<?php

namespace App\Http\Controllers;

use App\Application\EmpleadorService;
use App\Models\Empleador;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EmpleadorController extends Controller {
  /**
   * @var EmpleadorService
   */
  protected $service;

  function __construct(EmpleadorService $service)
  {
    $this->service = $service;
  }
  
  function buscar(Request $request): JsonResponse{
    $filter = $request->filter;
    $page = $request->page;

    [$total, $records] = $this->service->buscar($filter, $page);
    return  response()->json([
      "meta"=> ["total" => $total],
      "records" => $records
    ]);
  }

  function buscarPorPatronal(Request $request): JsonResponse {
    $empleador = $this->service->buscarPorPatronal($request->numero_patronal);
    if($empleador)
      return response()->json($empleador);
    throw new ModelNotFoundException("Empleador no encontrado");
  }
}