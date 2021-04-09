<?php

namespace App\Http\Controllers;

use App\Application\ListaMoraService;
use App\Models\ListaMoraItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ListaMoraController extends Controller {

  protected $listaMoraService;
  function __construct()
  {
    $this->listaMoraService = new ListaMoraService();
  } 

  function buscar(Request $request): JsonResponse{

    $query = ListaMoraItem::query();

    $filter = $request->filter;

    $page = $request->page;
    $pageSize = Arr::get($page, "size", 10); 
    $currentPage = Arr::get($page, "current", 1);
    $pagination=[
      "size" => $pageSize,
      "current" => $currentPage
    ];

    [$total, $records] = $this->listaMoraService->buscar($filter, $pagination);
    
    return response()->json([
      "meta" => [
        "total" => $total,
        "page" => [
          "size" => $pageSize,
          "current" => $currentPage
        ]
      ],
      "records" => $records
    ]);
  }

  function agregar(Request $request): JsonResponse {
    $payload = $request->validate([
      "empleador_id" => "required|numeric"
    ]);

    $item  = $this->listaMoraService->agregar($payload["empleador_id"]);

    return response()->json($item);
  }

  function quitar(Request $request): JsonResponse {
    $payload = $request->validate([
      "empleador_id" => "required|numeric"
    ]);
    $this->listaMoraService->quitar($payload["empleador_id"]);
    return response()->json();
  }
}