<?php

namespace App\Http\Controllers;

use App\Application\EmpleadorService;
use App\Models\Empleador;
use App\Models\Galeno\Empleador as GalenoEmpleador;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EmpleadorController extends Controller
{

    function appendFilters($query, $filter)
    {
        if ($numeroPatronal = Arr::get($filter, "numero_patronal")) {
            $query->where("NUMERO_PATRONAL_EMP", $numeroPatronal);
        }
        if (Arr::has($filter, "id")) {
            $query->where("ID", $filter["id"]);
        } else if (Arr::has($filter, "ids")) {
            $query->whereIn("ID", $filter["ids"]);
        }
    }

    function buscar(Request $request): JsonResponse
    {
        $filter = $request->filter;
        $page = $request->page;

        $query = GalenoEmpleador::query();

        return  $this->buildResponse($query, $filter, $page);
    }

    function buscarPorPatronal(Request $request): JsonResponse
    {
        $empleador = GalenoEmpleador::where("NUMERO_PATRONAL_EMP", $request->numero_patronal)->first(); //$this->service->buscarPorPatronal($request->numero_patronal);
        if ($empleador)
            return response()->json($empleador);
        throw new ModelNotFoundException("Empleador no encontrado");
    }
}
