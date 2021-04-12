<?php

namespace App\Http\Controllers;

use App\Application\SolicitudAtencionExternaService;
use App\Http\Reports\Dm11Generador;
use App\Models\SolicitudAtencionExterna;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    [$total, $solicitudes] = $this->solicitudAtencionExternaService->buscar($filter, $page);
    return response()->json([
      "meta" => [
        "total" => $total
      ],
      "records" => $solicitudes
    ]);
  }

  function registrar(Request $request){
    $payload = $request->validate([]);    
    return response()->json();
  }

  function generarDm11(Request $request, string $numeroSolicitud): JsonResponse {
    $solicitud = $this->solicitudAtencionExternaService->generarDatosParaFormularioDm11($numeroSolicitud);
    $dm11Generator = new Dm11Generador();
    $url = $dm11Generator->generar($solicitud);
    $this->solicitudAtencionExternaService->actualizarUrlDm11($numeroSolicitud, $url);
    return response()->json([
      "url" => $url
    ]);
  }

  function VerDm11(Request $request, string $numeroSolicitud): BinaryFileResponse {
    return response()->file(storage_path("app/formularios/dm11/${numeroSolicitud}.pdf"), [
      "Access-Control-Allow-Origin" => "*",
      "Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS",
      "Access-Control-Allow-Headers", "X-Requested-With, Content-Type, X-Token-Auth, Authorization"
    ]);
  }
}