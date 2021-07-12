<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AreaController extends Controller {
  function buscar(Request $request) {
    return response()->json(Area::get());
  }
}