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
        $query->with("especialidad", "contrato.prestaciones");

        if (Arr::has($filter, "activos")) {
            if($filter["activos"]){
                $query->whereHas("contrato");
            }
            else {
                $query->whereDoesntHave("contrato");
            }
        }
        if (($tipos = Arr::get($filter, "tipos")) && count($tipos)){
            $query->whereIn("tipo_id", $tipos);
        }
        if ($nombre = Arr::get($filter, "nombre")){
            $query->whereRaw("MATCH(`nombre`, `apellido_paterno`, `apellido_materno`, `nombres`) AGAINST(? IN BOOLEAN MODE)", [$nombre . "*"]);
        }
        if (($prestaciones_id = Arr::get($filter, "prestaciones_id")) && count($prestaciones_id)) {
            $query->whereHas("contrato", function ($query) use($prestaciones_id) {
                $sub = DB::table('prestaciones_contratadas')
                    ->select('prestacion_id')
                    ->whereColumn('contratos_proveedores.id', 'contrato_id');
                foreach($prestaciones_id as $id){
                    $query->whereRaw("? IN ({$sub->toSql()})", [$id]);
                }
            });
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

        $proveedor->load(["contratos"]);
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
                "contrato.prestacion_ids" => "array|required"
            ], [
                "general.apellido_paterno.required_without" => "Debe indicar al menos un apellido",
                "general.apellido_materno.required_without" => "Debe indicar al menos un apellido"
            ]);
            $this->authorize("registrar", [Proveedor::class, $payload]);
            $proveedor = DB::transaction(function () use ($payload) {
                @["general" => $general, "contacto" => $contacto, "contrato" => $contrato] = $payload;
                $proveedor = Proveedor::create([
                    "tipo_id" => 1,
                    "nit" => $general["nit"]??null,
                    "ci" => $general["ci"],
                    "ci_complemento" => $general["ci_complemento"]??null,
                    "apellido_paterno" => $general["apellido_paterno"],
                    "apellido_materno" => $general["apellido_materno"],
                    "nombres" => $general["nombres"],
                    "especialidad_id" => $general["especialidad_id"],
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
                $prestacion_ids = $contrato["prestacion_ids"]??[];
                $contratoModel->prestaciones()->attach($prestacion_ids);
                return $proveedor;
            });
            $proveedor->load(["contratos"]);
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
                $prestacion_ids = $contrato["prestacion_ids"] ?? [];
                $contratoModel->prestaciones()->attach($prestacion_ids);
                return $proveedor;
            });
            $proveedor->load(["contratos"]);
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
            $proveedor->update([
                "nit" => $payload["nit"] ?? null,
                "ci" => $payload["ci"],
                "ci_complemento" => $payload["ci_complemento"] ?? null,
                "apellido_paterno" => $payload["apellido_paterno"],
                "apellido_materno" => $payload["apellido_materno"],
                "nombres" => $payload["nombres"],
                "especialidad_id" => $payload["especialidad_id"],
                "regional_id" => $payload["regional_id"]
            ]);
            $proveedor->refresh()->load(["contratos"]);
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
            $proveedor->refresh()->load(["contratos"]);
            return response()->json($proveedor);
        }
    }

    function buscarContrato(Request $request, $proveedorId){
        $page = $request->page;
        $filter = $request->filter;

        $proveedor = Proveedor::find($proveedorId);
        if(!$proveedor)
            throw new ModelNotFoundException("El proveedor no existe");

        $this->authorize("ver", $proveedor);

        $query = $proveedor->contratos();

        if($desde = Arr::get($filter, "desde")) {
            $query->where("inicio", ">=", $desde);
        }
        if($hasta = Arr::get($filter, "hasta")) {
            $query->where("inicio", "<=", $hasta);
        }

        if ($page && Arr::has($page, "size")) {
            $total = $query->count();
            $query->limit($page["size"]);
            if (Arr::has($page, "current")) {
                $query->offset(($page["current"] - 1) * $page["size"]);
            }
        }
        return response()->json($this->buildPaginatedResponseData($total, $query->get()));
    }

    function verContrato(Request $request, $idProveedor, $id){
        $proveedor = Proveedor::find($idProveedor);
        if (!$proveedor) {
            throw new ModelNotFoundException("El proveedor no existe");
        }
        $contrato = ContratoProveedor::find($id);
        $this->authorize("ver", $proveedor);

        $contrato->load(["prestaciones"]);
        return response()->json($contrato);
    }

    function registrarContrato(Request $request, $proveedorId)
    {
        $proveedor = Proveedor::find($proveedorId);

        if (!$proveedor) {
            throw new ModelNotFoundException("Proveedor no existe");
        }

        $this->authorize("registrar-contrato", $proveedor);

        $payload = $request->validate([
            "inicio" => "required|date",
            "fin" => "nullable|date"
        ]);

        $contrato = $proveedor->contratos()
            ->whereDate("inicio", "<=", $payload["fin"])
            ->where(function($query) use($payload) {
                $query->whereDate("fin", ">=", $payload["fin"])->orWhereNull("fin");
            })
            ->where("estado", 1)
            ->first();
        if ($contrato) {
            throw ConflictException::withData("Este proveedor tiene un contrato que se superpone al rango de fechas indicado", $contrato->id);
        }

        $prestaciones_ids = $request->validate([
            "prestacion_ids" => "required|array"
        ])["prestacion_ids"] ?? [];

        $contrato = DB::transaction(function () use ($proveedor, $payload, $prestaciones_ids) {
            $contrato = $proveedor->contratos()->create($payload);
            $contrato->prestaciones()->attach($prestaciones_ids);
            return $contrato;
        });

        return response()->json($contrato);
    }    

    function consumirContrato(Request $request, $proveedorId, $contratoId)
    {
        $proveedor = Proveedor::find($proveedorId);

        if (!$proveedor) {
            throw new ModelNotFoundException("Proveedor no existe");
        }

        $contrato = $proveedor->contratos()->where("id", $contratoId)->first();
        if(!$contrato) {
            throw new ModelNotFoundException("Contrato no existe");
        }
        
        $this->authorize("consumir-contrato", $proveedor);

        abort_if(!!$contrato->fin, 400, "Solo los contratos indefinidos pueden ser consumidos");

        $contrato->estado = ContratoProveedor::CONSUMIDO;
        $contrato->save();

        return response()->json($contrato);
    }    

    function extenderContrato(Request $request, $proveedorId, $contratoId)
    {
        $proveedor = Proveedor::find($proveedorId);
        if (!$proveedor) {
            throw new ModelNotFoundException("Proveedor no existe");
        }
        
        $this->authorize("extender-contrato", $proveedor);

        $contrato = $proveedor->contratos()->where("id", $contratoId)->first();
        if(!$contrato) {
            throw new ModelNotFoundException("Contrato no existe");
        }
        
        abort_if($contrato->estado == 3, 400, "No puede extender contratos anulados");
        $now = Carbon::now();
        $extensionActual = $contrato->extension ?? $contrato->fin ?? $now;
        if($contrato->fin) {
            abort_if($extensionActual->gt($now), 400, "Solo puede extender contratos a partir de su fecha de finalización");
        }
        else {
            abort_if($contrato->extension ? $contrato->extension->gt($now) : !$contrato->consumido, 400, "Solo puede extender contratos ya consumidos");
        }

        $contrato->extension = $extensionActual->addWeek();
        $contrato->save();

        return response()->json($contrato);
    }

    function anularContrato(Request $request, $proveedorId, $contratoId)
    {
        $proveedor = Proveedor::find($proveedorId);
        if (!$proveedor) {
            throw new ModelNotFoundException("Proveedor no existe");
        }
        
        $this->authorize("anular-contrato", $proveedor);

        $contrato = $proveedor->contratos()->where("id", $contratoId)->first();
        if(!$contrato) {
            throw new ModelNotFoundException("Contrato no existe");
        }

        $contrato->estado = ContratoProveedor::ANULADO;
        $contrato->save();

        return response()->json($contrato);
    }

    function actualizarInformacionContacto(Request $request, $proveedorId) {
        $proveedor = Proveedor::find($proveedorId);
        if (!$proveedor) {
            throw new ModelNotFoundException("El proveedor no existe");
        }
        $payload = $request->validate([
            "municipio_id" => "required|numeric",
            "direccion" => "required",
            "ubicacion" => "required",
            "ubicacion.latitud" => "required|numeric",
            "ubicacion.longitud" => "required|numeric",
            "telefono1" => "required|numeric",
            "telefono2" => "nullable|numeric",
        ]);
        $this->authorize("editar", [$proveedor, $payload]);
        $proveedor->municipio_id = $payload["municipio_id"];
        $proveedor->direccion = $payload["direccion"];
        $proveedor->ubicacion = new Point($payload["ubicacion"]["latitud"], $payload["ubicacion"]["longitud"]);
        $proveedor->telefono1 = $payload["telefono1"];
        $proveedor->telefono2 = $payload["telefono2"] ?? null;

        $proveedor->save();

        return response()->json($proveedor);
    }
}
