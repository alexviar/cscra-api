<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get("/env", function(){
  dd($_ENV);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get("/log", function(){
  return response()->download(storage_path("/logs/laravel.log"));
});

Route::get("/phpinfo", function(){
  phpinfo();
});

Route::get("host", function(Request $request){
  return $request->getHost();
});

Route::get("sanctum-config", function(Request $request){
  return config("sanctum");
});

Route::fallback(function () {
  //return view('react.index.html')
  return File::get(public_path() . "/build/index.html");
});