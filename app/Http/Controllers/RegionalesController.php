<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Regional;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RegionalesController extends Controller {
  function obtener(Request $request) {
    return response()->json(Regional::get());
  }
}