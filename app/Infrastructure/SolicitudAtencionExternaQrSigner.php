<?php

namespace App\Infrastructure;

use App\Infrastructure\CWT\COSEHeaders;
use App\Infrastructure\CWT\CWT;
use App\Infrastructure\CWT\Sign1Message;
use App\Models\SolicitudAtencionExterna;
use CBOR\ByteStringObject;
use CBOR\ListObject;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\TextStringObject;
use CBOR\UnsignedIntegerObject;
use EllipticCurve\PrivateKey;
use EllipticCurve\PublicKey;
use Mhauri\Base45;

class SolicitudAtencionExternaQrSigner {

    /**
     * @param SolicitudAtencionExterna $solicitud Solicitud de atencion externa para la cual se generará un qr firmado
     * @param string $key Clave ECDSA SHA256 para firmar el qr de la solicitud.
     * 
     * @return string Devuelve un CBOR Web Token firmado
     */
    function sign($solicitud, $key){
        // $cwt = new CWT();
        // return $cwt->setSubject(TextStringObject::create($solicitud->asegurado->matriculaCompleta))
        //     ->setAudience(TextStringObject::create($solicitud->proveedor))
        //     ->setExpiration(UnsignedIntegerObject::create($solicitud->fecha->add(7, "days")->timestamp))
        //     ->setIssuedAt(UnsignedIntegerObject::create($solicitud->fecha->timestamp))
        //     ->addCustomClaim(-65537, ListObject::create()
        //         ->add(TextStringObject::create($solicitud->asegurado()->nombreCompleto))
        //         ->add(TextStringObject::create($solicitud->prestacionesSolicitadas->prestacion))
        //     )->encode($key);
        $msg = new Sign1Message(
            ByteStringObject::create(
                MapObject::create()
                    ->add(UnsignedIntegerObject::create(COSEHeaders::ALGORITHM), NegativeIntegerObject::create(-7))
                    ->__toString()
            ),
            MapObject::create(),
            ByteStringObject::create(
                MapObject::create()
                ->add(UnsignedIntegerObject::create(CWT::SUB_KEY), TextStringObject::create($solicitud->asegurado->matriculaCompleta))
                ->add(UnsignedIntegerObject::create(CWT::AUD_KEY), TextStringObject::create($solicitud->proveedor))
                ->add(UnsignedIntegerObject::create(CWT::EXP_KEY), UnsignedIntegerObject::create($solicitud->fecha->add(7, "days")->timestamp))
                ->add(UnsignedIntegerObject::create(CWT::IAT_KEY), UnsignedIntegerObject::create($solicitud->fecha->timestamp))
                ->add(NegativeIntegerObject::create(-65537), 
                    ListObject::create()
                        ->add(TextStringObject::create($solicitud->asegurado->nombreCompleto))
                        ->add(TextStringObject::create($solicitud->prestacionesSolicitadas[0]->prestacion))
                )
                ->__toString()                
            )
        );

        $token = $msg->encode(PrivateKey::fromPem($key));

        $compresed = zlib_encode($token, ZLIB_ENCODING_DEFLATE);
        
        $base45 = new \Mhauri\Base45();

        return $base45->encode($compresed);
    }

    function validate($raw, $key){
        $decoded = (new Base45())->decode($raw);
        $uncompressed = zlib_decode($decoded);
        $msg = Sign1Message::decode($uncompressed);
        return $msg->validate(PublicKey::fromPem($key));
    }

}