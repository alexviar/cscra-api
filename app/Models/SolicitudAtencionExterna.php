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

    protected $table = "atenciones_externas";

    protected $casts = [
        "fecha" => "datetime:d/m/y h:i:s"
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

    function registradoPor()
    {
        return $this->belongsTo(User::class, "usuario_id", "id");
    }

    function regional()
    {
        return $this->belongsTo(Regional::class, "regional_id");
    }

    function prestacionesSolicitadas()
    {
        return $this->hasMany(PrestacionSolicitada::class, "transferencia_id");
    }

    function toArray()
    {
        $array = parent::toArray();
        $array["fecha"] = $this->fecha->format("d/m/y h:i:s");
        $array['url_dm11'] = $this->urlDm11;
        return $array;
    }
}
