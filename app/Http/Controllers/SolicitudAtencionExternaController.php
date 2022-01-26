<?php

namespace App\Http\Controllers;

use App\Application\SolicitudAtencionExternaService;
use App\Http\Reports\Dm11Generador;
use App\Models\Galeno\Afiliado;
use App\Models\SolicitudAtencionExterna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SolicitudAtencionExternaController extends Controller
{

    /** @var */
    private $solicitudAtencionExternaService;

    function __construct()
    {
        $this->solicitudAtencionExternaService = new SolicitudAtencionExternaService();
    }

    function buscar(Request $request): JsonResponse
    {
        $filter = $request->filter ?: [];
        $page = $request->page ?: [];
        $this->authorize("ver-todo", [SolicitudAtencionExterna::class, $filter]);

        [$total, $solicitudes] = $this->solicitudAtencionExternaService->buscar($filter, $page);
        return response()->json([
            "meta" => [
                "total" => $total
            ],
            "records" => $solicitudes
        ]);
    }

    function registrar(Request $request)
    {
        // dd($request->user()->toArray());
        $payload = $request->validate([
            "regional_id" => "required|exists:regionales,id",
            "paciente_id" => "required|exists:".Afiliado::class.",ID",
            "medico_id" => "required|exists:medicos,id",
            "proveedor_id" => "required|exists:proveedores,id",
            "prestacion" => "required|max:100"
        ]);
        $this->authorize("registrar", [SolicitudAtencionExterna::class, $payload]);
        
        $solicitud = $this->solicitudAtencionExternaService->registrar(
            $payload["regional_id"],
            $payload["asegurado_id"],
            $payload["medico_id"],
            $payload["proveedor_id"],
            $request->user()->id,
            $payload["prestacion"]
        );
        // if(Gate::allows("ver-dm11", SolicitudAtencionExterna::class)){
        $dm11Generator = new Dm11Generador();
        $dm11Generator->generar($solicitud);
        // }
        return response()->json($solicitud);
    }

    function verDm11(Request $request, string $numeroSolicitud): BinaryFileResponse
    {
        $solicitud = SolicitudAtencionExterna::find(intval($numeroSolicitud));
        $this->authorize("ver-dm11", $solicitud);
        if (!Storage::exists("formularios/dm11/${numeroSolicitud}.pdf")) {
            $dm11Generator = new Dm11Generador();
            $dm11Generator->generar($solicitud);
        }
        return response()->file(storage_path("app/formularios/dm11/${numeroSolicitud}.pdf"), [
            "Access-Control-Allow-Origin" => "*",
            "Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS",
            "Access-Control-Allow-Headers", "X-Requested-With, Content-Type, X-Token-Auth, Authorization"
        ]);
        // return response()->stream()
    }
}
