<?php

namespace App\Http\Controllers;

use App\Application\AseguradoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AseguradosController extends Controller {
  private $service;
  function __construct()
  {
    $this->service = new AseguradoService();
  }
  
  function buscar(Request $request): JsonResponse {
    [$total, $records] = $this->service->buscar($request->filter, $request->page, explode(",", $request->include));
    return response()->json([
      "meta" => [
        "total" => $total,
      ],
      "records" => $records
    ]);
  }
}