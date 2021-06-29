<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EspecialidadesController extends Controller {

  function buscar(Request $request): JsonResponse {
    $filter = $request->filter;
    $page = $request->page;
    $this->authorize("verTodo", Especialidad::class);

    $query = Especialidad::query();
    // $query->whereRaw("1");
    if(Arr::has($filter, "nombre") && $nombre=$filter["nombre"]){
    //   $query->where("nombre", "LIKE", "{$nombre}%");
        $query->whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$nombre."*"]);
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

//   function ver(Request $request, int $id){
//     $especialidad = Especialidad::find($id);
//     if($especialidad)
//       return response()->json($especialidad);
//     throw new ModelNotFoundException("especialidad no encontrada");
//   }

  function registrar(Request $request){
    $prestacionClass = Especialidad::class;
    $payload = $request->validate([
      "nombre" => "required|unique:{$prestacionClass}"
    ]);

    $this->authorize("registrar", Especialidad::class);

    $especialidad = Especialidad::create($payload);
    return response()->json($especialidad);
  }

  function actualizar(Request $request, int $id){
    $prestacionClass = Especialidad::class;
    $payload = $request->validate([
      "nombre" => "required|unique:{$prestacionClass},nombre,{$id}"
    ]);

    $especialidad = Especialidad::find($id);
    
    if(!$especialidad)
        throw new ModelNotFoundException("especialidad no encontrada");
    $this->authorize("editar", $especialidad);
    $especialidad->nombre = $payload["nombre"];
    $especialidad->save();
    return response()->json($especialidad);
  }

  function eliminar(Request $request, int $id){
    $this->authorize("eliminar", Especialidad::class);
    Especialidad::destroy($id);
    return response()->json();
  }

//   function importar(Request $request): JsonResponse {
//     $archivo = $request->file("archivo");
//     // var_dump($archivo, $archivo->getPathname());
//     $this->especialidadesService->importar($archivo->getPathname(), $request->separador, $request->formato);
//     return response()->json();
//   }
}