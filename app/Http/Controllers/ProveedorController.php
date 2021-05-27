<?php

namespace App\Http\Controllers;

use App\Exceptions\ConflictException;
use App\Http\Controllers\Controller;
use App\Models\ContratoProveedor;
use App\Models\Medico;
use App\Models\Prestacion;
use App\Models\Proveedor;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProveedorController extends Controller
{
    function buscar(Request $request)
    {
        $page = $request->page;
        $filter = $request->filter;

        $this->authorize("verTodo", [Proveedor::class, $filter]);

        $query = Proveedor::query();
        $query->with("medico.especialidad", "contrato.prestaciones");

        if (Arr::get($filter, "activos", 0)) {
            $query->whereHas("contrato");
        }

        if ($page && Arr::has($page, "size")) {
            $total = $query->count();
            $query->limit($page["size"]);
            if (Arr::has($page, "current")) {
                $query->offset(($page["current"] - 1) * $page["size"]);
            }
            return response()->json($this->buildPaginatedResponseData($total, $query->get()));
        }
        if (Arr::has($page, "current")) {
            $query->offset($page["current"]);
        }
        return response()->json($query->get());
    }

    function buscarPorNombre(Request $request)
    {
        // $prestaciones = $request->nombre ? Prestacion::where("nombre", "like", $request->nombre . "%" )->get() : [];
        $records = [];
        if ($request->nombre) {
            $records = Proveedor::buscarPorNombre($request->nombre);
        }
        return response()->json($records);
    }

    function mostrar(Request $request, $id)
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) {
            throw new ModelNotFoundException("El proveedor no existe");
        }
        $this->authorize("ver", $proveedor);

        $proveedor->load(["medico", "contratos"]);
        return response()->json($proveedor);
    }

    function registrar(Request $request)
    {
        $tipo_id = $request->general["tipo_id"];
        if ($tipo_id == 1) {
            $payload = $request->validate([
                "general.nit" => "nullable",
                "general.ci" => "required",
                "general.ci_complemento" => "nullable",
                "general.apellido_paterno" => "required_without:apellido_materno",
                "general.apellido_materno" => "required_without:apellido_paterno",
                "general.nombres" => "required",
                "general.especialidad_id" => "required",
                "general.regional_id" => "required",
                "contacto" => "nullable",
                "contacto.municipio_id" => "required_unless:contacto,null|numeric",
                "contacto.direccion" => "required_unless:contacto,null",
                "contacto.ubicacion" => "required_unless:contacto,null",
                "contacto.ubicacion.latitud" => "required_unless:contacto,null|numeric",
                "contacto.ubicacion.longitud" => "required_unless:contacto,null|numeric",
                "contacto.telefono1" => "required_unless:contacto,null|numeric",
                "contacto.telefono2" => "nullable|numeric",
                "contrato.inicio" => "date|required",
                "contrato.fin" => "nullable|date",
                // "contrato.regional_id" => "numeric|required",
                "contrato.prestacion_ids" => "array|required"
            ], [
                "general.apellido_paterno.required_without" => "Debe indicar al menos un apellido",
                "general.apellido_materno.required_without" => "Debe indicar al menos un apellido"
            ]);
            $this->authorize("registrar", [Proveedor::class, $payload]);
            $proveedor = DB::transaction(function () use ($payload) {
                @["general" => $general, "contacto" => $contacto, "contrato" => $contrato] = $payload;
                $medico = Medico::create([
                    "ci" => $general["ci"],
                    "ci_complemento" => $general["ci_complemento"],
                    "apellido_paterno" => $general["apellido_paterno"],
                    "apellido_materno" => $general["apellido_materno"],
                    "nombres" => $general["nombres"],
                    "especialidad_id" => $general["especialidad_id"],
                    "regional_id" => $general["regional_id"],
                    "tipo" => 2
                ]);
                $proveedor = Proveedor::create([
                    "tipo_id" => 1,
                    "nit" => $general["nit"],
                    "regional_id" => $general["regional_id"],
                    "medico_id" => $medico->id,
                    "municipio_id" => $contacto["municipio_id"] ?? null,
                    "direccion" => $contacto["direccion"] ?? null,
                    "ubicacion" => $contacto ? new Point($contacto["ubicacion"]["latitud"], $contacto["ubicacion"]["longitud"]) : null,
                    "telefono1" => $contacto["telefono1"] ?? null,
                    "telefono2" => $contacto["telefono2"] ?? null
                ]);

                $contratoModel = $proveedor->contratos()->create([
                    "inicio" => $contrato["inicio"],
                    "fin" => $contrato["fin"] ?? null,
                    // "regional_id" => $contrato["regional_id"]
                ]);
                $prestacion_ids = $contrato["prestacion_ids"];
                $contratoModel->prestaciones()->attach($prestacion_ids);
                return $proveedor;
            });
            $proveedor->load(["medico", "contratos"]);
            return response()->json($proveedor);
        } else if ($tipo_id == 2) {
            $payload = $request->validate([
                "general.nit" => "nullable",
                "general.nombre" => "required",
                "general.regional_id" => "numeric|required",
                "contacto" => "nullable",
                "contacto.municipio_id" => "required_unless:contacto,null|numeric",
                "contacto.direccion" => "required_unless:contacto,null",
                "contacto.ubicacion" => "required_unless:contacto,null",
                "contacto.ubicacion.latitud" => "required_unless:contacto,null|numeric",
                "contacto.ubicacion.longitud" => "required_unless:contacto,null|numeric",
                "contacto.telefono1" => "required_unless:contacto,null|numeric",
                "contacto.telefono2" => "nullable|numeric",
                "contrato.inicio" => "date|required",
                "contrato.fin" => "nullable|date",
                // "contrato.regional_id" => "numeric|required",
                "contrato.prestacion_ids" => "array|required"
            ]);

            $this->authorize("registrar", [Proveedor::class, $payload]);
            $proveedor = DB::transaction(function () use ($payload) {
                @["general" => $general, "contacto" => $contacto, "contrato" => $contrato] = $payload;
                $proveedor = Proveedor::create([
                    "tipo_id" => 2,
                    "nit" => $general["nit"],
                    "nombre" => $general["nombre"],
                    "regional_id" => $general["regional_id"],
                    "municipio_id" => $contacto["municipio_id"] ?? null,
                    "direccion" => $contacto["direccion"] ?? null,
                    "ubicacion" => $contacto ? new Point($contacto["ubicacion"]["latitud"], $contacto["ubicacion"]["longitud"]) : null,
                    "telefono1" => $contacto["telefono1"] ?? null,
                    "telefono2" => $contacto["telefono2"] ?? null
                ]);
                $contratoModel = $proveedor->contratos()->create([
                    "inicio" => $contrato["inicio"],
                    "fin" => $contrato["fin"] ?? null,
                    // "regional_id" => $contrato["regional_id"]
                ]);
                $prestacion_ids = $contrato["prestacion_ids"];
                $contratoModel->prestaciones()->attach($prestacion_ids);
                return $proveedor;
            });
            $proveedor->load(["medico", "contratos"]);
            return response()->json($proveedor);
        } else {
            abort(400);
        }
    }

    function actualizar(Request $request, $id)
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) {
            throw new ModelNotFoundException("El proveedor no existe");
        }
        if ($proveedor->tipo_id == 1) {
            $payload = $request->validate([
                "nit" => "nullable",
                "ci" => "required",
                "ci_complemento" => "nullable",
                "apellido_paterno" => "required_without:apellido_materno",
                "apellido_materno" => "required_without:apellido_paterno",
                "nombres" => "required",
                "especialidad_id" => "required",
                "regional_id" => "required"
            ], [
                "apellido_paterno.required_without" => "Debe indicar al menos un apellido",
                "apellido_materno.required_without" => "Debe indicar al menos un apellido"
            ]);

            $this->authorize("actualizar", [$proveedor, $payload]);
            DB::transaction(function () use ($proveedor, $payload) {
                $proveedor->medico->update([
                    "ci" => $payload["ci"],
                    "ci_complemento" => $payload["ci_complemento"],
                    "apellido_paterno" => $payload["apellido_paterno"],
                    "apellido_materno" => $payload["apellido_materno"],
                    "nombres" => $payload["nombres"],
                    "especialidad_id" => $payload["especialidad_id"],
                    "regional_id" => $payload["regional_id"]
                ]);
                $proveedor->update([
                    "nit" => $payload["nit"],
                    "regional_id" => $payload["regional_id"],
                ]);
                $proveedor->refresh()->load(["medico", "contratos"]);
            });
            return response()->json($proveedor);
        } else {
            $payload = $request->validate([
                "nit" => "nullable",
                "nombre" => "required",
                "regional_id" => "required"
            ]);

            $this->authorize("actualizar", [$proveedor, $payload]);
            $proveedor->update([
                "nit" => $payload["nit"],
                "nombre" => $payload["nombre"],
                "regional_id" => $payload["regional_id"],
            ]);
            $proveedor->refresh()->load(["medico", "contratos"]);
            return response()->json($proveedor);
        }
    }

    function registrarContrato(Request $request, $proveedorId)
    {
        $proveedor = Proveedor::find($proveedorId);

        if (!$proveedor) {
            throw new ModelNotFoundException("Proveedor no existe");
        }

        $payload = $request->validate([
            "inicio" => "required|date",
            "fin" => "required|date"
        ]);

        $contrato = $proveedor->contratos()->whereDate("inicio", "<=", $payload["fin"])->whereDate("fin", ">=", $payload["fin"])->first();
        if ($contrato) {
            throw ConflictException::withData("Este proveedor tiene un contrato que se superpone al rango de fechas indicado", $contrato);
        }

        $prestaciones_ids = $request->validate([
            "prestacion_ids" => "required|array"
        ])["prestacion_ids"];

        $contrato = DB::transaction(function () use ($proveedor, $payload, $prestaciones_ids) {
            $contrato = $proveedor->contratos()->create($payload);
            $contrato->prestaciones()->attach($prestaciones_ids);
            return $contrato;
        });

        return response()->json($contrato);
    }
}
