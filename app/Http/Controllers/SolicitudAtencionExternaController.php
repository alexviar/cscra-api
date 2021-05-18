<?php

namespace App\Http\Controllers;

use App\Application\SolicitudAtencionExternaService;
use App\Http\Reports\Dm11Generador;
use App\Models\SolicitudAtencionExterna;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SolicitudAtencionExternaController extends Controller {

  /** @var */
  private $solicitudAtencionExternaService;

  function __construct()
  {
    $this->solicitudAtencionExternaService = new SolicitudAtencionExternaService();
  }

  function buscar(Request $request): JsonResponse {
    $filter = $request->filter ?: [];
    $page = $request->page ?: [];
    $this->authorize("ver-todo", [SolicitudAtencionExterna::class, $filter]);
    
    if(!$request->user()->can("ver solicitudes de atencion externa")){
      if($request->user()->can("ver unicamente solicitudes de atencion externa registradas por el usuario")){
        $filter["usuario_id"] = $request->user()->id;
      }
      if($request->user()->can("ver unicamente solicitudes de atencion externa de la misma regional")){
        $filter["regional_id"] = $request->user()->regional_id;
      }
    }

    [$total, $solicitudes] = $this->solicitudAtencionExternaService->buscar($filter, $page);
    return response()->json([
      "meta" => [
        "total" => $total
      ],
      "records" => $solicitudes
    ]);
  }

  function registrar(Request $request){
    $this->authorize("registrar", [SolicitudAtencionExterna::class, $request->regional_id]);
    $payload = $request->validate([
      "regional_id" => "required",
      "asegurado_id" => "required",
      "medico_id" => "required",
      "proveedor_id" => "required",
      "prestaciones_solicitadas" => "array | required",
      "prestaciones_solicitadas.*.prestacion_id" => "required",
      "prestaciones_solicitadas.*.nota" => "nullable"
    ]);    
    $solicitud = $this->solicitudAtencionExternaService->registrar(
      $payload["regional_id"], 
      $payload["asegurado_id"],
      $payload["medico_id"],
      $payload["proveedor_id"],      
      $request->user()->id,
      $payload["prestaciones_solicitadas"]
    );
    // if(Gate::allows("ver-dm11", SolicitudAtencionExterna::class)){
      $dm11Generator = new Dm11Generador();
      $dm11Generator->generar($solicitud);
    // }
    return response()->json($solicitud);
  }

  function verDm11(Request $request, string $numeroSolicitud): BinaryFileResponse {
    $this->authorize("ver-dm11", SolicitudAtencionExterna::class);
    if(!Storage::exists("formularios/dm11/${numeroSolicitud}.pdf")){
      $solicitud = SolicitudAtencionExterna::find(intval($numeroSolicitud));
      $dm11Generator = new Dm11Generador();
      $dm11Generator->generar($solicitud);
    }
    return response()->file(storage_path("app/formularios/dm11/${numeroSolicitud}.pdf"), [
      "Access-Control-Allow-Origin" => "*",
      "Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS",
      "Access-Control-Allow-Headers", "X-Requested-With, Content-Type, X-Token-Auth, Authorization"
    ]);
  }
}