<?php

namespace App\Http\Controllers;

use App\Http\Reports\Dm11Generador;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Medico;
use App\Models\Proveedor;
use App\Models\SolicitudAtencionExterna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SolicitudAtencionExternaController extends Controller
{
    function appendFilters($query, $filter)
    {
        if($busqueda = Arr::get($filter, "_busqueda")){
            if(is_numeric($busqueda)) $query->where("id", $busqueda);
            else $query->whereRaw("MATCH(`prestacion`) AGAINST(? IN BOOLEAN MODE)", [$busqueda."*"]);
        }
        if($regionalId = Arr::get($filter, "regional_id")) {
            $query->where("regional_id", $regionalId);
        }
        if($proveedorId = Arr::get($filter, "proveedor_id")){
            if(Str::startsWith($proveedorId, "EMP")){
                $query->where("proveedor_id", Str::substr($proveedorId, 3)); 
            }
            else if(Str::startsWith($proveedorId, "MED")){
                $query->where("proveedor_id", Str::substr($proveedorId, 3));                
            }
            else{
                $query->where("proveedor_id", $proveedorId);
            }
        }
        if($medicoId = Arr::get($filter, "medico_id")){
            $query->where("medico_id", $medicoId);
        }
        if (($matricula = Arr::get($filter, "matricula"))) {
            $asegurados = Afiliado::buscarPorMatricula($matricula);
            $query->where(function($query) use($asegurados) {
                $ids = $asegurados->pluck("ID");
                $query->whereIn("paciente_id", $ids)->orWhereIn("titular_id", $ids);
            });
        }
        if (($numeroPatronal = Arr::get($filter, "numero_patronal"))) {
            $empleador = Empleador::buscarPorPatronal($numeroPatronal);
            $query->where("empleador_id", $empleador->id);
        }
        if (($regionalId = Arr::get($filter, "regional_id"))) {
            $query->where("regional_id", $regionalId);
        }
        if (($desde = Arr::get($filter, "desde"))) {
            $query->whereDate("fecha", ">=", $desde);
        }
        if (($hasta = Arr::get($filter, "hasta"))) {
            $query->whereDate("fecha", "<=", $hasta);
        }
    }

    function buscar(Request $request): JsonResponse
    {
        $filter = $request->filter ?: [];
        $page = $request->page ?: [];

        $this->authorize("ver-todo", [SolicitudAtencionExterna::class, $filter]);

        return $this->buildResponse(
            SolicitudAtencionExterna::query()->with(["paciente", "titular", "empleador", "medico", "proveedor", "regional"]),
            $filter, $page
        );
    }

    function validateVigenciaDeDerecho($asegurado, $currentDate)
    {
        $errors = [];
        $hoy = $currentDate;
        if (!$asegurado) {
            $errors["asegurado"] = "El asegurado no existe";
        } else if (!$asegurado->afiliacion) {
            $errors["asegurado.afiliacion"] = "No se encontraron registros de la afiliacion";
        } else {
            if ($asegurado->estado == 1) {
                if ($asegurado->afiliacion->baja) $errors["asegurado.estado"] = "El asegurado figura como activo, pero existe registro de su baja";
            } else if ($asegurado->estado == 2) {
                if (!$asegurado->afiliacion->baja) $errors["asegurado.estado"] = "El asegurado figura como dado de baja, pero no se enontraron registros de la baja";
            } else {
                $errors["asegurado.estado"] = "El asegurado tiene un estado indeterminado";
            }

            if ($asegurado->afiliacion->baja) {
                if (!$asegurado->fechaValidezSeguro) $errors["asegurado.fecha_validez_seguro"] = "Fecha no especificada, se asume que el seguro ya no tiene validez";
                else if ($asegurado->fechaValidezSeguro->lte($hoy)) $errors["asegurado.fecha_validez_seguro"] = "El seguro ya no tiene validez";
            }
            if ($asegurado->fechaExtincion && $asegurado->fechaExtincion->lte($hoy)) {
                $errors["asegurado.fecha_extincion"] = "Fecha de extincion alcanzada";
            }

            if ($asegurado->tipo == 2) {
                $titular = $asegurado->titular;
                // var_dump($titular->getAttributes());
                if(!$titular){
                    $errors["titular"] = "Titular no encontrado";
                }
                if($asegurado->afiliacion->parentesco != 8){
                    if ($titular->estado == 1) {
                        if ($asegurado->afiliacionDelTitular->baja) $errors["titular.estado"] = "El asegurado figura como activo, pero existe registro de su baja";
                    } else if ($titular->estado == 2) {
                        if (!$asegurado->afiliacionDelTitular->baja) $errors["titular.estado"] = "El asegurado figura como dado de baja, pero no se enontraron registros de la baja";
                    } else {
                        $errors["titular.estado"] = "El asegurado tiene un estado indeterminado";
                    }
    
                    if ($titular->afiliacion->baja) {
                        if (!$titular->afiliacion->baja->fechaValidezSeguro) $errors["titular.fecha_validez_seguro"] = "Fecha no especificada, se asume que el seguro ya no tiene validez";
                        else if ($titular->afiliacion->baja->fechaValidezSeguro->lte($hoy)) $errors["titular.fecha_validez_seguro"] = "El seguro ya no tiene validez";
                    }
                }
            }

            $empleador = $asegurado->empleador;
            if ($empleador->estado == 1) {
                if ($empleador->fecha_baja) $errors["empleador.estado"] = "El empleador figura como activo, pero tiene una fecha de baja";
            } else if ($empleador->estado == 2 || $empleador->estado == 3) {
                if (!$empleador->fecha_baja) $errors["empleador.fecha_baja"] = "No se ha especificado la fecha de baja, se asume que el seguro ya no tiene validez";
                else if ($empleador->fecha_baja->addMonths(2)->lte($hoy)) $errors["empleador.fecha_baja"] = "El seguro ya no tiene validez";
            } else {
                $errors["empleador.estado"] = "El empleador tiene un estado indeterminado";
            }
            if(ListaMoraItem::where("empleador_id", $empleador->id)->exists()){
                $errors["empleador.aportes"] = "El empleador esta en mora";
            }
        }
        return $errors;
    }

    function validateMedico($medico, $payload){
        $errors = [];
        if (!$medico) {
            $errors["medico"] = "El médico no existe";
        } else if ($medico->regional_id !== $payload["regional_id"]) {
            $errors["medico"] = "El médico pertenece a otra regional";
        }
        return $errors;
    }

    function validateProveedor($proveedor, $payload){
        $errors = [];
        if (!$proveedor) {
            $errors["proveedor"] = "El proveedor no existe";
        } else if ($proveedor->regional_id !== $payload["regional_id"]) {
            $errors["proveedor"] = "El proveedor pertenece a otra regional";
        }
        return $errors;
    }

    function registrar(Request $request)
    {
        // dd($request->user()->toArray());
        $payload = $request->validate([
            "regional_id" => "required|exists:regionales,id",
            // Por que gastar tiempo en una consulta para ver si existe si 
            // de cualquier manera necesito recuperar el registro para 
            // validar la vigencia de derecho?. Mejor hacer solo una consulta.
            "paciente_id" => "required",//"required|exists:".Afiliado::class.",ID",
            "medico_id" => "required",//|exists:medicos,id",
            "proveedor_id" => "required",//|exists:proveedores,id",
            "prestacion" => "required|max:100"
        ]);
        
        $asegurado = Afiliado::buscarPorId($payload["paciente_id"]);
        $hoy = Carbon::now();
        $erroresVigenciaDeDerecho = $this->validateVigenciaDeDerecho($asegurado, $hoy);
        $medico = Medico::find($payload["medico_id"]);
        $erroresMedico = $this->validateMedico($medico, $payload);
        $proveedor = Proveedor::findById($payload["proveedor_id"]);
        $erroresProveedor = $this->validateProveedor($proveedor, $payload);

        $errores = array_merge($erroresVigenciaDeDerecho, $erroresMedico, $erroresProveedor);
        if(count($errores)) throw ValidationException::withMessages($errores);

        $this->authorize("registrar", [SolicitudAtencionExterna::class, $payload]);

        
        $solicitud = SolicitudAtencionExterna::create([
            "fecha" => $hoy,
            "prestacion" => $payload["prestacion"],
            "regional_id" => $payload["regional_id"],
            "paciente_id" => $asegurado->id,
            "titular_id" => $asegurado->titular ? $asegurado->titular->id : null,
            "empleador_id" => $asegurado->empleador->id,
            "medico_id" => $medico->id,
            "proveedor_id" => $proveedor->id,
            "user_id" => $request->user()->id
        ]);
        // if(Gate::allows("ver-dm11", SolicitudAtencionExterna::class)){
        $dm11Generator = new Dm11Generador();
        $dm11Generator->generar($solicitud);
        // }
        // $solicitud->load(["paciente", "titular", "empleador", "medico", "proveedor"]);
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
