<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use App\Models\Regional;
use App\Models\ValueObjects\CarnetIdentidad;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{

    protected function appendFilters($query, $filter)
    {
        if ($busqueda = Arr::get($filter, "_busqueda")) {
            $query->where(function ($query) use ($busqueda) {
                $query->whereRaw("MATCH(`apellido_paterno`, `apellido_materno`, `nombre`) AGAINST(? IN BOOLEAN MODE)", [$busqueda . "*"]);
                $query->orWhere("especialidad", $busqueda);
            });
            if (($tipo = Arr::get($filter, "tipo"))) {
                $query->where("tipo", $tipo);
            }
        } else {
            if ($nombre = Arr::get($filter, "nombre")) {
                $query->whereRaw("MATCH(`apellido_paterno`, `apellido_materno`, `nombre`) AGAINST(? IN BOOLEAN MODE)", [$nombre . "*"]);
            }
            if (($nit = Arr::get($filter, "nit"))) {
                $query->where("nit", $nit);
            }
            if (($tipo = Arr::get($filter, "tipo"))) {
                $query->where("tipo", $tipo);
                if ($tipo == 1) {
                    if($ciRaiz = Arr::get($filter, "ci.raiz")){
                        $query->where("ci", $ciRaiz);
                        if($ciComplemento = Arr::get($filter, "ci.complemento")) $query->where("ci_complemento", $ciComplemento);
                    }
                }
            }
        }
        if ($estado = Arr::get($filter, "estado")) {
            $query->where("estado", $estado);
        }
        if (($regional_id = Arr::get($filter, "regional_id"))) {
            $query->where("regional_id", $regional_id);
        }
    }

    function buscar(Request $request)
    {
        $page = $request->page;
        $filter = $request->filter;

        $this->authorize("verTodo", [Proveedor::class, $filter]);

        return $this->buildResponse(Proveedor::query(), $filter, $page);
    }

    function mostrar(Request $request, $id)
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) {
            throw new ModelNotFoundException("El proveedor no existe");
        }
        $this->authorize("ver", $proveedor);

        return response()->json($proveedor);
    }

    private function registrarMedico(Request $request)
    {
        $payload = $request->validate([
            "nit" => ["required", Rule::unique('proveedores')->where(function ($query) use ($request) {
                return $query->where('regional_id', $request->regional_id);
            })],
            "ci" => [function ($attribute, $value, $fail) use ($request) {
                $user = Proveedor::where("ci", $value["raiz"])
                    ->where("ci_complemento", $value["complemento"] ?? "")
                    ->where("regional_id", $request->regional_id)
                    ->first();
                if ($user) {
                    $fail("Ya existe un proveedor registrado con este carnet de identidad.");
                }
            }],
            "ci.raiz" => "required|integer",
            "ci.complemento" => "nullable|regex:/^[1-9][A-Z]$/",
            "apellido_paterno" => "required_without:apellido_materno|max:25",
            "apellido_materno" => "required_without:apellido_paterno|max:25",
            "nombre" => "required|max:50",
            "especialidad" => "required",
            "regional_id" => "required|exists:" . Regional::class . ",id",
            "direccion" => "required",
            "ubicacion.latitud" => "required|numeric|between:-90,90",
            "ubicacion.longitud" => "required|numeric|between:-180,180",
            "telefono1" => "required|integer",
            "telefono2" => "nullable|integer"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido",
            "ci.complemento.regex" => "Complemento invalido.",
            "nit.unique" => "Ya existe un proveedor registrado con este NIT.",
            "regional_id.exists" => "Regional inválida."
        ]);

        $this->authorize("registrar", [Proveedor::class, $payload]);

        $proveedor = Proveedor::create(Arr::except($payload, ["ci", "ci_complemento", "ubicacion"]) + [
            "tipo" => 1,
            "estado" => 1,
            "ci" => new CarnetIdentidad(Arr::get($payload, "ci.raiz"), Arr::get($payload, "ci.complemento") ?? ""),
            "ubicacion" => new Point(Arr::get($payload, "ubicacion.latitud"), Arr::get($payload, "ubicacion.longitud"))
        ]);
        return $proveedor;
    }


    private function registrarEmpresa(Request $request)
    {
        $payload = $request->validate([
            "nit" => ["required", Rule::unique('proveedores')->where(function ($query) use ($request) {
                return $query->where('regional_id', $request->regional_id);
            })],
            "nombre" => "required|max:100",
            "regional_id" => "required|exists:" . Regional::class . ",id",
            "direccion" => "required",
            "ubicacion.latitud" => "required|numeric|between:-90,90",
            "ubicacion.longitud" => "required|numeric|between:-180,180",
            "telefono1" => "required|integer",
            "telefono2" => "nullable|integer"
        ], [
            "nit.unique" => "Ya existe un proveedor registrado con este NIT.",
            "regional_id.exists" => "Regional inválida."
        ]);

        $this->authorize("registrar", [Proveedor::class, $payload]);

        return Proveedor::create(Arr::except($payload, ["ubicacion"]) + [
            "tipo" => 2,
            "estado" => 1,
            "ubicacion" => new Point(Arr::get($payload, "ubicacion.latitud"), Arr::get($payload, "ubicacion.longitud"))
        ]);
    }

    function registrar(Request $request)
    {
        ["tipo" => $tipo] = $request->validate(["tipo" => "required|in:1,2"], ["tipo.in" => "Tipo invalido"]);
        if ($tipo == 1) {
            $proveedor = $this->registrarMedico($request);
        } else if ($tipo == 2) {
            $proveedor = $this->registrarEmpresa($request);
        }

        $proveedor->load("regional");
        return response()->json($proveedor);
    }

    private function actualizarMedico(Request $request, $proveedor)
    {
        $payload = $request->validate([
            "nit" => ["required", Rule::unique('proveedores')->ignore($proveedor->id)->where(function ($query) use ($request) {
                return $query->where('regional_id', $request->regional_id);
            })],
            "ci" => [function ($attribute, $value, $fail) use ($request, $proveedor) {
                $user = Proveedor::where("ci", $value["raiz"])
                    ->where("ci_complemento", $value["complemento"] ?? "")
                    ->where("regional_id", $request->regional_id)
                    ->where("id", "<>", $proveedor->id)
                    ->first();
                if ($user) {
                    $fail("Ya existe un proveedor registrado con este carnet de identidad.");
                }
            }],
            "ci.raiz" => "required|integer",
            "ci.complemento" => "nullable|regex:/^[1-9][A-Z]$/",
            "apellido_paterno" => "required_without:apellido_materno|max:25",
            "apellido_materno" => "required_without:apellido_paterno|max:25",
            "nombre" => "required|max:50",
            "especialidad" => "required",
            "regional_id" => "required|exists:" . Regional::class . ",id",
            "direccion" => "required",
            "ubicacion.latitud" => "required|numeric|between:-90,90",
            "ubicacion.longitud" => "required|numeric|between:-180,180",
            "telefono1" => "required|integer",
            "telefono2" => "nullable|integer"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido",
            "ci.complemento.regex" => "Complemento invalido.",
            "nit.unique" => "Ya existe un proveedor registrado con este NIT.",
            "regional_id.exists" => "Regional inválida."
        ]);

        $this->authorize("actualizar", [$proveedor, $payload]);

        $proveedor->update(Arr::except($payload, ["ci", "ci_complemento", "ubicacion"]) + [
            "ci" => new CarnetIdentidad(Arr::get($payload, "ci.raiz"), Arr::get($payload, "ci.complemento") ?? ""),
            "ubicacion" => new Point(Arr::get($payload, "ubicacion.latitud"), Arr::get($payload, "ubicacion.longitud"))
        ]);
    }


    private function actualizarEmpresa(Request $request, $proveedor)
    {
        $payload = $request->validate([
            "nit" => ["required", Rule::unique('proveedores')->ignore($proveedor->id)->where(function ($query) use ($request) {
                return $query->where('regional_id', $request->regional_id);
            })],
            "nombre" => "required|max:100",
            "regional_id" => "required|exists:" . Regional::class . ",id",
            "direccion" => "required",
            "ubicacion.latitud" => "required|numeric|between:-90,90",
            "ubicacion.longitud" => "required|numeric|between:-180,180",
            "telefono1" => "required|integer",
            "telefono2" => "nullable|integer"
        ], [
            "nit.unique" => "Ya existe un proveedor registrado con este NIT.",
            "regional_id.exists" => "Regional inválida."
        ]);

        $this->authorize("actualizar", [$proveedor, $payload]);

        $proveedor->update(Arr::except($payload, ["ubicacion"]) + [
            "ubicacion" => new Point(Arr::get($payload, "ubicacion.latitud"), Arr::get($payload, "ubicacion.longitud"))
        ]);
    }

    function actualizar(Request $request, $id)
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) {
            throw new ModelNotFoundException("El proveedor no existe");
        }

        if ($proveedor->tipo == 1) {
            $this->actualizarMedico($request, $proveedor);
        } else {
            $this->actualizarEmpresa($request, $proveedor);
        }

        $proveedor->load("regional");
        return response()->json($proveedor);
    }

    function actualizarEstado(Request $request, $id)
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) {
            throw new ModelNotFoundException("El proveedor no existe");
        }
        $payload = $request->validate([
            "estado" => "required|in:1,2"
        ], [
            "estado.in" => "Estado invalido"
        ]);

        $this->authorize("actualizar-estado", [$proveedor, $payload]);

        $proveedor->estado = $payload["estado"];
        $proveedor->save();

        return response()->json($proveedor);
    }
}
