<?php

namespace App\Http\Controllers;

use App\Models\SolicitudAtencionExterna;
use Illuminate\Http\Request;

class AutocompletadoController extends Controller {

    function autocompleteMedicos(Request $request){
        $text = $request->text;
        $page = $request->page;
        
        // $query = SolicitudAtencionExterna::whereRaw("MATCH(`medico`) AGAINST(? IN BOOLEAN MODE)", [$text . "*"])->distinct();
        $query = SolicitudAtencionExterna::where("medico", "LIKE", $text . "%")->distinct();
        
        $total = $query->count();

        $query->offset(max($page["current"] - 1, 0) * $page["size"])
            ->limit($page["size"]);

        $records = $query->get();

        return response()->json($this->buildPaginatedResponseData($total, $records->pluck("medico")));
    }

    function autocompleteProveedores(Request $request){
        $text = $request->text;
        $page = $request->page;
        
        $query = SolicitudAtencionExterna::whereRaw("MATCH(`proveedores`) AGAINST(? IN BOOLEAN MODE)", [$text . "*"]);
        
        $total = $query->count();

        $query->offset(max($page["current"] - 1, 0) * $page["size"])
            ->limit($page["size"]);

        $records = $query->get();

        return response()->json($this->buildPaginatedResponseData($total, $records));
    }
}