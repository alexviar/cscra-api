<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medico;
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
}