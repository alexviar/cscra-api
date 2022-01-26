<?php

namespace App\Http\Reports;

use App\Infrastructure\SolicitudAtencionExternaQrSigner;
use App\Models\SolicitudAtencionExterna;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class Dm11Generador {

  function generar(SolicitudAtencionExterna $solicitud){

    $content = $this->getContentArray($solicitud);

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

  
  function getContentArray($solicitud)
  {
      $asegurado = $solicitud->asegurado;
      $titular = $asegurado->titular; //afiliacionDelTitular ? Afiliado::buscarPorId($asegurado->afiliacionDelTitular->ID_AFO) : NULL;
      $empleador = $solicitud->empleador;

      $encoded_qr_data = (new SolicitudAtencionExternaQrSigner())->sign($solicitud, config("app.private_ec_key"));

      return [
          "numero" => $solicitud->numero,
          "qr_data" => $encoded_qr_data,
          "fecha" => $solicitud->fecha->format("d/m/y H:i:s"),
          "regional" => strtoupper($solicitud->regional->nombre),
          "proveedor" => $solicitud->proveedor,
          "titular" => !$titular ? [
              "matricula" => [$asegurado->matricula, $asegurado->matricula_complemento],
              "nombre" => $asegurado->nombre_completo
          ] : [
              "matricula" => [$titular->matricula, $titular->matricula_complemento],
              "nombre" => $titular->nombre_completo
          ],
          "beneficiario" => !$titular ? [
              "matricula" => ["", ""],
              "nombre" => ""
          ] : [
              "matricula" => [$asegurado->matricula, $asegurado->matricula_complemento],
              "nombre" => $asegurado->nombre_completo
          ],
          "empleador" => $empleador->nombre,
          "doctor" => [
              "nombre" => $solicitud->medico,
              "especialidad" => $solicitud->especialidad
          ],
          "prestaciones" => $solicitud->prestacionesSolicitadas->map(function ($prestacionSolicitada) {
              return $prestacionSolicitada->prestacion;
          })->chunk(ceil($solicitud->prestacionesSolicitadas->count() / 3))
      ];
  }
}