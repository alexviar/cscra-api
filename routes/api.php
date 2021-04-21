<?php

use App\Http\Controllers\EmpleadorController;
use App\Http\Controllers\AseguradosController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\ListaMoraController;
use App\Http\Controllers\MedicosController;
use App\Http\Controllers\PrestacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RegionalesController;
use App\Http\Controllers\SolicitudAtencionExternaController;
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


Route::middleware("auth:sanctum")->get("empleadores", [EmpleadorController::class, "buscar"]);
Route::middleware("auth:sanctum")->get("empleadores/buscar-por-patronal", [EmpleadorController::class, "buscarPorPatronal"]);
Route::middleware("auth:sanctum")->get("asegurados", [AseguradosController::class, "buscar"]);

Route::middleware("auth:sanctum")->get("lista-mora", [ListaMoraController::class, "buscar"]);
Route::middleware("auth:sanctum")->post("lista-mora/agregar", [ListaMoraController::class, "agregar"]);
Route::middleware("auth:sanctum")->post("lista-mora/quitar", [ListaMoraController::class, "quitar"]);

Route::middleware("auth:sanctum")->get("especialidades", [EspecialidadesController::class, "buscar"]);
Route::middleware("auth:sanctum")->post("especialidades/importar", [EspecialidadesController::class, "importar"]);

Route::middleware("auth:sanctum")->get("solicitudes-atencion-externa", [SolicitudAtencionExternaController::class, "buscar"]);
Route::middleware("auth:sanctum")->post("solicitudes-atencion-externa", [SolicitudAtencionExternaController::class, "registrar"]);
Route::middleware("auth:sanctum")->get("formularios/dm11/{numero}", [SolicitudAtencionExternaController::class, "verDm11"])
  ->where('id', '[0-9]{10}')->name("forms.dm11");
Route::middleware("auth:sanctum")->put("solicitudes-atencion-externa/{id}/generar-dm11", [SolicitudAtencionExternaController::class, "generarDm11"])
  ->where('id', '[0-9]{10}');

Route::middleware("auth:sanctum")->get("medicos", [MedicosController::class, "buscar"]);

Route::middleware("auth:sanctum")->get("regionales", [RegionalesController::class, "obtener"]);

Route::middleware("auth:sanctum")->get("prestaciones", [PrestacionController::class, "buscar"]);
Route::middleware("auth:sanctum")->get("prestaciones/{id}", [PrestacionController::class, "ver"]);
Route::middleware("auth:sanctum")->post("prestaciones", [PrestacionController::class, "registrar"]);
Route::middleware("auth:sanctum")->put("prestaciones/{id}", [PrestacionController::class, "actualizar"]);
Route::middleware("auth:sanctum")->delete("prestaciones/{id}", [PrestacionController::class, "eliminar"]);
Route::middleware("auth:sanctum")->post("prestaciones/importar", [PrestacionController::class, "importar"]);
Route::middleware("auth:sanctum")->get("prestaciones/buscar-nombre", [PrestacionController::class, "buscarPorNombre"]);

Route::middleware("auth:sanctum")->get("proveedores/buscar-nombre", [ProveedorController::class, "buscarPorNombre"]);