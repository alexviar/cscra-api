<?php

namespace App\Models;

use App\Infrastructure\SolicitudAtencionExternaQrSigner;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use Carbon\Carbon;
use CBOR\ByteStringObject;
use CBOR\ListObject;
use CBOR\NegativeIntegerObject;
use CBOR\TextStringObject;
use CBOR\UnsignedIntegerObject;
use EllipticCurve\Ecdsa;
use EllipticCurve\PrivateKey;
use Faker\Provider\Base;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property Carbon $fecha Fecha de emision de la solicitud de atencion externa
 */
class SolicitudAtencionExterna extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = "solicitudes_atencion_externa";

    protected $casts = [
        "fecha" => "datetime:d/m/y H:i:s"
    ];

    function getUrlDm11Attribute() {
        return route("forms.dm11", [
            "numero" => $this->numero
        ]);
    }

    function getNumeroAttribute()
    {
        return str_pad($this->id, 10, '0', STR_PAD_LEFT);
    }

    function paciente()
    {
        return $this->belongsTo(Afiliado::class, "paciente_id", "ID");
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
        return $this->belongsTo(Medico::class);
    }

    function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    function registradoPor()
    {
        return $this->belongsTo(User::class, "login", "id");
    }

    function regional()
    {
        return $this->belongsTo(Regional::class, "regional_id");
    }

    function toArray()
    {
        $array = parent::toArray();
        $array["fecha"] = $this->fecha->format("d/m/y H:i:s");
        $array['url_dm11'] = $this->urlDm11;
        return $array;
    }
}
