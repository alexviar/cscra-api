<?php

use App\Http\Controllers\EmpleadorController;
use App\Http\Controllers\ListaMoraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get("empleadores", [EmpleadorController::class, "buscar"]);

Route::get("lista-mora", [ListaMoraController::class, "buscar"]);
Route::post("lista-mora/agregar", [ListaMoraController::class, "agregar"]);
Route::post("lista-mora/quitar", [ListaMoraController::class, "quitar"]);
