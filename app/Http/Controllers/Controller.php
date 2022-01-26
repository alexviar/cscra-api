<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function appendFilters ($query, $filter) { }

    protected function buildPaginatedResponseData($meta, $records){
      return [
        "meta" => $meta,
        "records" => $records
      ];
    }

    protected function buildResponse($query, $filter, $page) {

        $this->appendFilters($query, $filter);

        $current = Arr::get($page, "current", 1);
        $size = Arr::get($page, "size");
        if ($size) {
            $total = $query->count();
            $query->limit($size);
            $query->offset(($current - 1) * $size);

            $meta = ["total" => $total];
            if($current * $size < $total) $meta["nextPage"] = $current + 1;
            if($current > 1) $meta["previousPage"] = $current - 1;

            return response()->json($this->buildPaginatedResponseData($meta, $query->get()));
        }

        return response()->json($query->get());
    }
}
