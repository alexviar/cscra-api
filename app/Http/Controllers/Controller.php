<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function appendFilters ($query, $filter) {

    }

    private function buildPaginatedResponseData($total, $records){
      return [
        "meta" => ["total" => $total],
        "records" => $records
      ];
    }

    protected function buildResponse($query, $filter, $page) {
        $this->appendFilters($query, $filter);
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
}
