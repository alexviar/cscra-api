<?php

namespace App\Http\Reports;

use App\Models\SolicitudAtencionExterna;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class Dm11Generador {

  function generar(SolicitudAtencionExterna $solicitud){

    $content = $solicitud->content_array;

    $pdf = PDF::loadView('pdf.dm11', $content);
    //$pdf->setEncryption('pwd')
    $pdf->setPaper('half-letter', "landscape");

    $numeroSolicitud = $solicitud->numero;
    Storage::put("formularios/dm11/{$numeroSolicitud}.pdf", $pdf->output());

    // $url = route("forms.dm11", [
    //   "numero" => $numeroSolicitud
    // ]);

    // $solicitud->update([
    //   "url_dm11" => $url
    // ]);
  }
}