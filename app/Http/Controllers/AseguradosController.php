<?php

namespace App\Http\Controllers;

use App\Models\Galeno\Afiliado;
use App\Models\ListaMoraItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AseguradosController extends Controller {
  
  // function buscar(Request $request): JsonResponse {
  //   [$total, $records] = $this->service->buscar($request->filter, $request->page, explode(",", $request->include));
  //   return response()->json([
  //     "meta" => [
  //       "total" => $total,
  //     ],
  //     "records" => $records
  //   ]);
  // }

  function buscar(Request $request) {
    $filter = $request->filter;
    $page = $request->page;

    $query = Afiliado::query()->with([
      "afiliacionesComoTitular.empleador",
      "afiliacionesComoTitular.baja",
      "afiliacionesComoBeneficiario.afiliacionDelTitular.afiliado",
      "afiliacionesComoBeneficiario.afiliacionDelTitular.empleador",
      "afiliacionesComoBeneficiario.afiliacionDelTitular.baja",
      "afiliacionesComoBeneficiario.baja"
    ]);

    if(Arr::has($filter, "matricula")){
      $query->where("MATRICULA", Str::upper($filter["matricula"]));
    }

    if($page && Arr::has($page, "size")){
      $total = $query->count();
      $query->limit($page["size"]);
      if(Arr::has($page, "current")){
        $query->offset(($page["current"] - 1) * $page["size"]);
      }

      $records = $query->get();

      return response()->json($this->buildPaginatedResponseData($total, $records));
    }
    if(Arr::has($page, "current")){
      $query->offset($page["current"]);
    }
    $records = $query->get();

    return response()->json($records);
  }
}