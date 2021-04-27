<?php

namespace App\Http\Controllers;

use App\Application\EspecialidadesService;
use App\Models\Especialidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EspecialidadesController extends Controller {

  /** @var  */
  private $especialidadesService;

  function __construct()
  {
    $this->especialidadesService = new EspecialidadesService();
  }
  function buscar(Request $request): JsonResponse {
    $filter = $request->filter;
    $page = $request->page;
    // $pageSize = Arr::get($page, "size", 10); 
    // $currentPage = Arr::get($page, "current", 1);
    // $pagination=[
    //   "size" => $pageSize,
    //   "current" => $currentPage
    // ];
    // [$total, $records] = $this->especialidadesService->buscar($filter, $pagination);
    // return response()->json([
    //   "meta" => [
    //     "total" => $total
    //   ],
    //   "records" => $records
    // ]);
    $query = Especialidad::query();
    // $query->whereRaw("1");
    if(Arr::has($filter, "nombre") && $nombre=$filter["nombre"]){
      $query->where("nombre", "LIKE", "{$nombre}%");
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

  function importar(Request $request): JsonResponse {
    $archivo = $request->file("archivo");
    // var_dump($archivo, $archivo->getPathname());
    $this->especialidadesService->importar($archivo->getPathname(), $request->separador, $request->formato);
    return response()->json();
  }
}