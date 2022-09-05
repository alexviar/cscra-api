<?php

namespace App\Http\Reports;

use App\Infrastructure\SolicitudAtencionExternaQrSigner;
use App\Models\SolicitudAtencionExterna;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class Dm11Generador
{

    function generar(SolicitudAtencionExterna $solicitud)
    {

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
        $asegurado = $solicitud->paciente;
        $titular = $asegurado->titular; //afiliacionDelTitular ? Afiliado::buscarPorId($asegurado->afiliacionDelTitular->ID_AFO) : NULL;
        $empleador = $solicitud->empleador;

        $encoded_qr_data = (new SolicitudAtencionExternaQrSigner())->sign($solicitud, config("app.private_ec_key"));

        $image = public_path("imgs/csc.png");
        $mime = getimagesize($image)["mime"];
        $data = file_get_contents($image);
        $dataUri = 'data:image/' . $mime . ';base64,' . base64_encode($data);
        
        return [
            "logo" => $dataUri,
            "numero" => $solicitud->numero,
            "qr_data" => $encoded_qr_data,
            "fecha" => $solicitud->fecha->format("d/m/y H:i:s"),
            "regional" => strtoupper($solicitud->regional->nombre),
            "proveedor" => [
                "razon_social" => $solicitud->proveedor->razon_social,
                "direccion" => $solicitud->proveedor->direccion,
                "telefono1" => $solicitud->proveedor->telefono1,
                "telefono2" => $solicitud->proveedor->telefono2,
            ],
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
                "nombre" => $solicitud->medico->nombre_completo,
                "especialidad" => $solicitud->especialidad
            ],
            "prestacion" => $solicitud->prestacion
        ];
    }
}
