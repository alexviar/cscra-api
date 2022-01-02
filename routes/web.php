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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('dm11', function(){
    return view("pdf.dm11", [
        "numero" => '00000000001',
        "qr_data" => 'HC1:6BFOXN%TSMAHN-H/WKO G3VOPARU5M0IIK.DVC2XTI1*VV 9Y73-33AFW*/F9HIE 0N3R+/377M7KQQF609BXAD-S4FT5D75W9AV88E34L/5E4FMIA$.B74DY7MOKE/*B.CJLZIB9J:13Y$SIOTJJS3WST$S.ZJZ0KXKITA2 JMF11VV2O-CXQF$73TA3:FJ-LJ5AL5:4A933NJIFT*EJDG3N*4HZ6U*9+E93ZM$96PZ6+Q6X46+E5%DPZ3IL613OUY*U7PPVGPV46RF6SH93T9*PPP%P0R6F1V4-HZ8EKK9+OCUF5NDC+G9QJPNF67J6QW6D9RVZMPK9O.0L35IWM7K81:6G16PCNQ+MBM6P84DYAU4796L5 8ZKANY4D64OBLA+M-T4MZIBSIX1J0DJKYJGCC:H3J1D2D3NXEKHG-+I+9O2IH/UK*II+JKZHH1LNRHH%TIBUIEJK1+GPIILJL3WMIKLEKN3OMQIATLCTIIFHH.HIZFEQWO8L52WDYF2Q:VNJAM7I88EWPV%SJ7EWKX6TOGG*2SQNNREE04F$C42SAR6QYELMSYRS3:EP/R0*NI*91VNW+PCCCRNP-BB.ZF1D1N40RY6$2',
        "fecha" => 'dd/mm/yyyy hh:ii:ss',
        "regional" => 'SANTA CRUZ',
        "proveedor" => 'CLINICA NUCLEAR',
        "titular" => [
            "matricula" => ['##-####-ABC', 0],
            "nombre" => 'COSME FULANITO'
        ],
        "beneficiario" => [
            "matricula" => ['##-####-ABC', 0],
            "nombre" => 'COSME FULANITO'
        ],
        "empleador" => 'BBVA PREVISION',
        "doctor" => [
            "nombre" => 'CHAPATIN',
            "especialidad" => 'MEDICO CIRUJANO'
        ],
        "prestaciones" => [[
            'Quimioterapia'
        ]]
    ]);
});

Route::fallback(function () {
  //return view('react.index.html')
  return File::get(public_path() . "/build/index.html");
});