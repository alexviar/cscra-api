<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;
use App\Models\Area;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PlanController extends Controller {

    function appendFilters($query, $filter)
    {
        if(($usuario_id = Arr::get($filter, "usuario_id"))) {
            $query->where("usuario_id", $usuario_id);
        }
    }

    function buscar(Request $request) {
        $page = $request->page;
        $filter = $request->filter;

        $this->authorize("verTodo", [Plan::class, $filter]);

        $query = Plan::query();
        $this->buildResponse($query, $filter, $page);
    }

    function ver(Request $request, Plan $plan) {
        $this->authorize("ver", $plan);
        // $plan = Plan::find($id);
        if(!$plan) {
            throw new ModelNotFoundException();
        }
        return response()->json($plan);
    }

    function registrar(Request $request) {
        $payload = $request->only(["objetivo_general", "actividades", "regional_id", "area_id"]);
        $this->authorize("registrar", [Plan::class, $payload]);
        Validator::make($payload, [
            "objetivo_general" => "required|max:150",
            "actividades" => "required|array",
            "actividades.*.nombre" => "required|max:150",
            "actividades.*.inicio" => "required|date",
            "actividades.*.fin" => "required|date|after_or_equal:inicio",
            "actividades.*.indicadores" => "required|max:1000",
            // "regional_id" => "required|exists:".Regional::class.",id",
            "area_id" => "required|exists:".Area::class.",id"
        ], [
            "objetivo_general.required" => "Debe indicar un objetivo general",
            "actividades.required" => "Debe indicar al menos una actividad",
            "actividades.*.nombre.required" => "Debe indicar un nombre",
            "actividades.*.inicio. required" => "Debe indicar una fecha de inicio",
            "actividades.*.fin.required" => "Debe indicar una fecha de fin",
            "actividades.*.fin.after_of_equal" => "La fecha de fin debe ser posterior o igual a la fecha de inicio",
            // "regional_id.required" => "Debe inciar una regional",
            // "regional_id.exists" => "Regional no valida",
            "area_id.required" => "Debe inciar una regional",
            "area_id.exists" => "Regional no valida"
        ]);

        $payload = array_merge($payload, [
            "usuario_id" => $request->user()->id,
            "regional_id" => $request->user()->regional_id
        ]);

        $plan = DB::transaction(function() use($payload) {
            $plan = Plan::create($payload);
            $plan->actividades()->create($payload["actividades"]);
        });

        return response()->json($plan);
    }
}