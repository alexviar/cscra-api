<?php

namespace App\Models;

use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudAtencionExterna extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = "atenciones_externas";

    function getUrlDm11Attribute() {
        return route("forms.dm11", [
            "numero" => $this->numero
        ]);
    }

    function getNumeroAttribute()
    {
        return str_pad($this->id, 10, '0', STR_PAD_LEFT);
    }

    function getSignatureAttribute()
    {
        $payload = pack("N", $this->id);
        $key = app()->make('config')->get('app.key');
        return hash_hmac("sha256", $payload, $key);
    }

    function validateSignature($signature)
    {
        return $this->signature == $signature;
    }

    function getContentArrayAttribute()
    {
        $asegurado = Afiliado::buscarPorId($this->asegurado_id);
        $titular = $asegurado->afiliacionDelTitular ? Afiliado::buscarPorId($asegurado->afiliacionDelTitular->ID_AFO) : NULL;
        $empleador = Empleador::buscarPorId($this->empleador_id);

        $signature = $this->signature;

        return [
            "numero" => $this->numero,
            "signature" => base64_encode($signature),
            "qr_data" => base64_encode(pack("N", $this->id) . $signature),
            "fecha" => $this->fecha,
            "regional" => $this->regional->nombre,
            "proveedor" => $this->proveedor->nombre,
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
                "nombre" => $this->medico->nombre_completo,
                "especialidad" => $this->medico->especialidad
            ],
            "proveedor" => $this->proveedor->nombre ?? $this->proveedor->nombreCompleto,
            "prestaciones" => $this->prestacionesSolicitadas->map(function ($prestacionSolicitada) {
                return $prestacionSolicitada->prestacion . ($prestacionSolicitada->nota ? " - " . $prestacionSolicitada->nota : "");
            })->chunk(ceil($this->prestacionesSolicitadas->count() / 3))
        ];
    }

    function asegurado()
    {
        return $this->belongsTo(Afiliado::class, "asegurado_id", "ID");
    }

    function titular()
    {
        return $this->belongsTo(Afiliado::class, "titular_id", "ID");
    }

    function empleador()
    {
        return $this->belongsTo(Empleador::class, "empleador_id", "ID");
    }

    function medico()
    {
        return $this->belongsTo(Medico::class, "medico_id");
    }

    function registradoPor()
    {
        return $this->belongsTo(User::class, "usuario_id", "id");
    }

    function proveedor()
    {
        return $this->belongsTo(Proveedor::class, "proveedor_id");
    }

    function regional()
    {
        return $this->belongsTo(Regional::class, "regional_id");
    }

    function prestacionesSolicitadas()
    {
        return $this->hasMany(PrestacionSolicitada::class, "transferencia_id");
    }
}
