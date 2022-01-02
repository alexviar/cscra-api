<?php

namespace App\Models;

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

    function _getSignatureStructure($protected, $payload) {
        return ListObject::create()
            ->add(TextStringObject::create("Signature1"))
            ->add($protected)
            ->add(ByteStringObject::create(""))
            ->add($payload);
    }

    function _computeSignature($protected, $payload) {
        $signature_structure = $this->_getSignatureStructure($protected, $payload);

        $privateKey = PrivateKey::fromPem(config("app.private_ec_key"));
        return Ecdsa::sign($signature_structure->__toString(), $privateKey)->toDer();
    }

    function encodeQrData($data, $extra_data)
    {
        $protected = \CBOR\ByteStringObject::create(
            \CBOR\MapObject::create([])
                ->add(UnsignedIntegerObject::create(1), NegativeIntegerObject::create(-7))
                ->__toString()
        );

        $unprotected = \CBOR\MapObject::create();

        $payload = \CBOR\ByteStringObject::create(
            \CBOR\MapObject::create()
                ->add(UnsignedIntegerObject::create(2), TextStringObject::create($data["sub"]))
                ->add(UnsignedIntegerObject::create(3), TextStringObject::create($data["aud"]))
                ->add(UnsignedIntegerObject::create(4), UnsignedIntegerObject::create($data["exp"]))
                ->add(UnsignedIntegerObject::create(6), UnsignedIntegerObject::create($data["iat"]))
                ->add(NegativeIntegerObject::create(-65537), ListObject::create()
                    ->add(TextStringObject::create($extra_data["paciente"]))
                    ->add(TextStringObject::create($extra_data["prestacion"]))
                )
                ->__toString()
        );

        $signature = ByteStringObject::create($this->_computeSignature($protected, $payload));

        $token = ListObject::create()
            ->add($protected)
            ->add($unprotected)
            ->add($payload)
            ->add($signature)->__toString();
        
        $compresed = zlib_encode($token, ZLIB_ENCODING_DEFLATE);
        
        $base45 = new \Mhauri\Base45();

        return $base45->encode($compresed);
    }

    function getQrData() {
        $asegurado = $this->asegurado;
        return [
            "data" => [
                "sub" => $asegurado->matricula . ($asegurado->matricula_complemento !== 0 ? "-" . $asegurado->matricula_complemento : ""),
                "aud" => $this->proveedor,
                "exp" => $this->fecha->add(7, "days")->timestamp,
                "iat" => $this->fecha->timestamp
            ], 
            "extra_data" => [
                "paciente" => $asegurado->nombre_completo,
                "prestacion" => $this->prestacionesSolicitadas[0]->prestacion
            ]
        ];
    }

    function __get($attr)
    {
        if($attr === "asegurado" && !isset($this->relations["asegurado"])){
            $this->relations["asegurado"] = Afiliado::buscarPorId($this->asegurado_id);
        } else if ($attr === "empleador" && !isset($this->relations["empleador"])){
            $this->relations["empleador"] = Empleador::buscarPorId($this->empleador_id);
        }
        return parent::__get($attr);
    }

    function getContentArrayAttribute()
    {
        $asegurado = $this->asegurado;
        $titular = $asegurado->titular; //afiliacionDelTitular ? Afiliado::buscarPorId($asegurado->afiliacionDelTitular->ID_AFO) : NULL;
        $empleador = $this->empleador;

        $qr_data = $this->getQrData();
        $encoded_qr_data = $this->encodeQrData($qr_data["data"], $qr_data["extra_data"]);

        return [
            "numero" => $this->numero,
            "qr_data" => $encoded_qr_data,
            "fecha" => $this->fecha,
            "regional" => $this->regional->nombre,
            "proveedor" => $this->proveedor,
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
                "nombre" => $this->medico,
                "especialidad" => $this->especialidad
            ],
            "prestaciones" => $this->prestacionesSolicitadas->map(function ($prestacionSolicitada) {
                return $prestacionSolicitada->prestacion;
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
        $array['url_dm11'] = $this->urlDm11;
        return $array;
    }
}
