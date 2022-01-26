<?php

namespace App\Http\Controllers;

use App\Models\Galeno\Empleador;
use App\Models\ListaMoraItem;
use App\Models\Regional;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ListaMoraController extends Controller
{
    protected function appendFilters($query, $filter)
    {
        if($busqueda = Arr::get($filter, "_busqueda")){
            // $query->whereRaw("MATCH(`numero_patronal`, `nombre`) AGAINST(? IN BOOLEAN MODE)", [$busqueda."*"]);
            $query->where(function ($query) use($busqueda){
                $query->whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$busqueda."*"])
                      ->orWhere("numero_patronal", "like", "{$busqueda}%");
            });
        }
        else{
            if($patronal = Arr::get($filter, "numero_patronal")){
                $query->where("numero_patronal", $patronal);
            }
            if ($nombre = Arr::get($filter, "nombre")) {
                // $query->where("nombre", "like", "%$nombre%");
                $query->whereRaw("MATCH(`nombre`) AGAINST(? IN BOOLEAN MODE)", [$nombre."*"]);
            }
        }
        if ($regionalId = Arr::get($filter, "regional_id")) {
            $query->where("regional_id", $regionalId);
        }
    }            

    function buscar(Request $request): JsonResponse
    {
        $page =  $request->page;
        $filter = $request->filter;

        $this->authorize("ver", [ListaMoraItem::class, $filter]);

        return $this->buildResponse(ListaMoraItem::query(), $filter, $page);
    }

    function agregar(Request $request): JsonResponse
    {
        $payload = $request->validate([
            "empleador_id" => ["required", "exists:" . Empleador::class . ",ID", Rule::unique(ListaMoraItem::class)]
        ], [
            "empleador_id.exists" => "El empleador no existe.",
            "empleador_id.unique" => "El empleador ya se encuentra en la lista de mora."
        ]);
        $empleador = Empleador::buscarPorId($payload["empleador_id"]);

        $this->authorize("agregar", [ListaMoraItem::class, $empleador]);

        $item = ListaMoraItem::create([
            "empleador_id" => $payload["empleador_id"],
            "numero_patronal" => $empleador->numero_patronal,
            "nombre" => $empleador->nombre,
            "regional_id" => Regional::mapGalenoIdToLocalId($empleador->ID_RGL)
        ]);

        return response()->json($item);
    }

    function quitar(Request $request, $id): JsonResponse
    {
        $item = ListaMoraItem::find($id);
        if (!$item)
            throw new ModelNotFoundException();

        $this->authorize("quitar", [ListaMoraItem::class, $item]);

        $item->delete();

        return response()->json();
    }
}
