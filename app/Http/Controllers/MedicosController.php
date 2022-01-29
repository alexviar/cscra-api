<?php

namespace App\Http\Controllers;

use App\Exceptions\ConflictException;
use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Regional;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MedicosController extends Controller
{
    protected function appendFilters($query, $filter)
    {
        if ($busqueda = Arr::get($filter, "_busqueda")) {
            $query->where(function ($query) use ($busqueda) {
                $query->whereRaw("MATCH(`apellido_paterno`, `apellido_materno`, `nombre`) AGAINST(? IN BOOLEAN MODE)", [$busqueda . "*"]);
                $query->orWhereRaw("MATCH(`especialidad`) AGAINST(? IN BOOLEAN MODE)", [$busqueda . "*"]);
            });
        } else {
            if ($nombre = Arr::get($filter, "nombre")) {
                $query->whereRaw("MATCH(`nombre`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$nombre]);
            }
            if ($ci = Arr::get($filter, "ci.raiz")) {
                $query->where("ci", $ci);
                if($ciComplemento = Arr::get($filter, "ci.complemento")) $query->where("ci_complemento", $ciComplemento);
            }
            if ($especialidad = Arr::get($filter, "especialidad")) {
                $query->whereRaw("MATCH(`especialidad`) AGAINST(? IN BOOLEAN MODE)", [$especialidad]);
            }
        }
        if (Arr::has($filter, "estado") && $estado = $filter["estado"]) {
            $query->where("estado", $estado);
        }
        if ($regionalId = Arr::get($filter, "regional_id")) {
            $query->where("regional_id", $regionalId);
        }
        // dd($query->toSql(), $filter);  
    }
    function buscar(Request $request)
    {
        $query = Medico::query();
        $page = $request->page;
        $filter = $request->filter;

        $this->authorize("verTodo", [Medico::class, $filter]);

        return $this->buildResponse($query, $filter, $page);
    }

    function mostrar(Request $request, $id)
    {
        $medico = Medico::find($id);
        $this->authorize("ver", $medico);
        if (!$medico) {
            throw new ModelNotFoundException("Medico no existe");
        }
        return response()->json($medico);
    }

    function registrar(Request $request)
    {
        $payload = $request->validate([
            "ci" => [function ($attribute, $value, $fail) use($request){
                $user = Medico::where("ci", $value["raiz"])
                    ->where("ci_complemento", $value["complemento"] ?? "")
                    ->where("regional_id", $request->regional_id)
                    ->first();
                if ($user) {
                    $fail("Ya existe un médico registrado con este carnet de identidad.");
                }
            }],
            "ci.raiz" => "required|integer",
            "ci.complemento" => "nullable|regex:/^[1-9][A-Z]$/",
            "apellido_paterno" => "required_without:apellido_materno|max:25",
            "apellido_materno" => "required_without:apellido_paterno|max:25",
            "nombre" => "required|max:50",
            "especialidad" => "required",
            "regional_id" => "required|exists:".Regional::class.",id"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido.",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido.",
            "regional_id.required" => "Debe indicar una regional.",
            "regional_id.exists" => "La regional no es válida.",
            "especialidad.required" => "Debe indicar una especialidad.",
        ]);

        $this->authorize("registrar", [Medico::class, $payload]);

        /** @var Medico $medico */
        $medico = Medico::create(
            array_merge($payload, [
                "estado" => 1,
                "ci" => new CarnetIdentidad(Arr::get($payload, "ci.raiz"), Arr::get($payload, "ci.complemento") ?? "")
            ])
        );
        $medico->load("regional");
        return response()->json($medico);
    }

    function actualizar(Request $request, $id)
    {
        /** @var Medico $medico */
        $medico = Medico::find($id);
        if (!$medico) {
            throw new ModelNotFoundException("Medico no existe");
        }

        $payload = $request->validate([
            "ci" => [function ($attribute, $value, $fail) use($request, $medico){
                $user = Medico::where("ci", $value["raiz"])
                    ->where("ci_complemento", $value["complemento"] ?? "")
                    ->where("regional_id", $request->regional_id)
                    ->where("id", "<>", $medico->id)
                    ->first();
                if ($user) {
                    $fail("Ya existe un médico registrado con este carnet de identidad.");
                }
            }],
            "ci.raiz" => "required|integer",
            "ci.complemento" => "nullable|regex:/^[1-9][A-Z]$/",
            "apellido_paterno" => "required_without:apellido_materno|max:25",
            "apellido_materno" => "required_without:apellido_paterno|max:25",
            "nombre" => "required|max:50",
            "especialidad" => "required",
            "regional_id" => "required|exists:".Regional::class.",id"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido.",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido.",
            "regional_id.required" => "Debe indicar una regional.",
            "regional_id.exists" => "La regional no es válida.",
            "especialidad.required" => "Debe indicar una especialidad.",
        ]);

        $this->authorize("editar", [$medico, $payload]);

        $medico->update(
            array_merge($payload, [
                "estado" => $medico->estado,
                "ci" => new CarnetIdentidad(Arr::get($payload, "ci.raiz"), Arr::get($payload, "ci.complemento") ?? "")
            ])
        );

        //Fresh model to refresh regional relationship
        $medico->load("regional");
        return response()->json($medico->fresh());
    }

    function actualizarEstado(Request $request, $id)
    {
        $medico = Medico::find($id);
        if (!$medico) {
            throw new ModelNotFoundException("Medico no existe");
        }
        
        $payload = $request->validate([
            "estado" => "required|in:1,2"
        ], [
            "estado.in" => "El estado es invalido",
            "estado.required" => "El estado es requerido"
        ]);

        $this->authorize("cambiar-estado", [$medico, $payload["estado"]]);

        $medico->update($payload);
        return response()->json($medico);
    }
}
