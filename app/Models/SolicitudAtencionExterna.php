<?php

namespace App\Models;

use App\Infrastructure\SolicitudAtencionExternaQrSigner;
use App\Models\Galeno\Afiliado;
use App\Models\Galeno\Empleador;
use App\Models\Traits\SaveToUpper;
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
    use HasFactory, SaveToUpper;

    protected $table = "solicitudes_atencion_externa";

    protected $appends = [ "url_dm11", "numero" ];

    protected $casts = [
        "fecha" => "datetime:d/m/y H:i:s"
    ];

    protected $fillable = [
        "fecha",
        "prestacion",
        "paciente_id",
        "titular_id",
        "empleador_id",
        "medico_id",
        "proveedor_id",
        "regional_id",
        "user_id"
    ];

    function getUrlDm11Attribute() {
        return route("forms.dm11", [
            "numero" => $this->numero
        ]);
    }

    function getNumeroAttribute()
    {
        return str_pad($this->id, 20, '0', STR_PAD_LEFT);
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

    function usuario()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    function regional()
    {
        return $this->belongsTo(Regional::class, "regional_id");
    }

    function toArray()
    {
        $array = parent::toArray();
        return Arr::undot(Arr::only(Arr::dot($array), [
            "id", "numero", "fecha", "prestacion", "url_dm11",
            "paciente.id", "paciente.matricula", "paciente.nombre_completo",
            "titular.id", "titular.matricula", "titular.nombre_completo",
            "empleador.id", "empleador.numero_patronal", "empleador.nombre",
            "medico.id", "medico.nombre_completo", "medico.especialidad",
            "proveedor.id", "proveedor.razon_social", "proveedor.especialidad",
            "usuario.id", "usuario.nombre",
            "regional.id", "regional.nombre"
        ]));
    }
}
