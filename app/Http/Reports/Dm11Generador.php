<?php

namespace App\Http\Reports;

use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class Dm11Generador {

  function generar($solicitud){
    $key = app()->make('config')->get('app.key');
    $payload= pack("N", $solicitud["numero"]);
    $solicitud["sign"] = hash_hmac("sha256", $payload, $key, true);
    $solicitud["qr_data"] = base64_encode($payload.$solicitud["sign"]);

    $pdf = PDF::loadView('pdf.dm11', $solicitud);
    //$pdf->setEncryption('pwd')
    $pdf->setPaper('half-letter', "landscape");

    $numeroSolicitud = $solicitud["numero"];
    Storage::put("formularios/dm11/{$numeroSolicitud}.pdf", $pdf->output());

    return url("formularios/dm11/{$numeroSolicitud}");
  }
}