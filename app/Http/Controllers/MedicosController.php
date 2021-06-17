<?php

namespace App\Http\Controllers;

use App\Exceptions\ConflictException;
use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Regional;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MedicosController extends Controller
{
    function buscar(Request $request)
    {
        $query = Medico::query();
        $page = $request->page;
        $filter = $request->filter;

        $this->authorize("verTodo", [Medico::class, $filter]);

        if (Arr::has($filter, "nombre_completo") && $nombre = $filter["nombre_completo"]) {
            $query->whereRaw("MATCH(`nombres`, `apellido_paterno`, `apellido_materno`) AGAINST(? IN BOOLEAN MODE)", [$nombre . "*"]);
        }
        if (Arr::has($filter, "ci") && $ci = $filter["ci"]) {
            $query->where("ci", $ci);
        }
        if (Arr::has($filter, "ci_complemento") && $ciComplemento = $filter["ci_complemento"]) {
            $query->where("ci_complemento", $ciComplemento);
        }
        if (Arr::has($filter, "especialidad_id") && $especialidad_id = $filter["especialidad_id"]) {
            $query->where("especialidad_id", $especialidad_id);
        }
        if (Arr::has($filter, "tipo") && $tipo = $filter["tipo"]) {
            $query->where("tipo", $tipo);
        }
        if (Arr::has($filter, "estado") && $estado = $filter["estado"]) {
            $query->where("estado", $estado);
        }
        if ($page && Arr::has($page, "size")) {
            $total = $query->count();
            $query->limit($page["size"]);
            if (Arr::has($page, "current")) {
                $query->offset(($page["current"] - 1) * $page["size"]);
            }
            $records = $query->get();
            $records->makeVisible("nombre_completo");
            return response()->json($this->buildPaginatedResponseData($total, $records));
        }
        if (Arr::has($page, "current")) {
            $query->offset($page["current"]);
        }
        $records = $query->get();
        $records->makeVisible("nombre_completo");
        return response()->json($records);
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
            "tipo" => "required|in:1,2",
            "ci" => "required|numeric",
            "ci_complemento" => "nullable",
            "apellido_paterno" => "required_without:apellido_materno",
            "apellido_materno" => "required_without:apellido_paterno",
            "nombres" => "required",
            "regional_id" => "required|exists:".Regional::class.",id",
            "especialidad_id" => "required|exists:".Especialidad::class.",id"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido.",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido.",
            "regional_id.required" => "Debe indicar una regional.",
            "regional_id.exists" => "La regional no es válida.",
            "especialidad_id.required" => "Debe indicar una especialidad.",
            "especialidad_id.exists" => "La especialidad no es válida."
        ]);

        $this->authorize("registrar", [Medico::class, $payload]);

        $exists = Medico::where("ci", $payload["ci"])
            ->where("ci_complemento", $payload["ci_complemento"] ?? null)
            ->first();
        if($exists){
            throw ConflictException::withData("Existe un registro con el mismo carnet de identidad.", $exists);
        }

        $medico = Medico::create($payload+["estado" => 1, "tipo" => 1]);
        return response()->json($medico);
    }

    function actualizar(Request $request, $id)
    {

        $payload = $request->validate([
            "tipo" => "required|in:1,2",
            "ci" => "required|numeric",
            "ci_complemento" => "nullable",
            "apellido_paterno" => "required_without:apellido_materno",
            "apellido_materno" => "required_without:apellido_paterno",
            "nombres" => "required",
            "regional_id" => "required|exists:".Regional::class.",id",
            "especialidad_id" => "required|exists:".Especialidad::class.",id"
        ], [
            "apellido_paterno.required_without" => "Debe indicar al menos un apellido.",
            "apellido_materno.required_without" => "Debe indicar al menos un apellido.",
            "regional_id.required" => "Debe indicar una regional.",
            "regional_id.exists" => "La regional no es válida.",
            "especialidad_id.required" => "Debe indicar una especialidad.",
            "especialidad_id.exists" => "La especialidad no es válida."
        ]);

        $medico = Medico::find($id);
        if (!$medico) {
            throw new ModelNotFoundException("Medico no existe");
        }

        $exists = Medico::where("ci", $payload["ci"])
            ->where("ci_complemento", $payload["ci_complemento"] ?? null)
            ->where("id", "<>", $id)
            ->first();
        if($exists){
            throw ConflictException::withData("Existe un registro con el mismo carnet de identidad.", $exists);
        }

        $this->authorize("editar", [$medico, $payload]);

        $medico->fill($payload);
        $medico->save();
        return response()->json($medico);
    }

    function cambiarEstado(Request $request, $id)
    {
        $payload = $request->validate([
            "estado" => "required|in:1,2"
        ], [
            "estado.in" => "El estado es invalido",
            "estado.required" => "El estado es requerido"
        ]);

        $medico = Medico::find($id);
        if (!$medico) {
            throw new ModelNotFoundException("Medico no existe");
        }
        $this->authorize("cambiar-estado", [$medico, $payload["estado"]]);

        $medico->update($payload);
        return response()->json($medico);
    }
}
