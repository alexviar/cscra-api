<?php

namespace App\Infrastructure\CWT;

use CBOR\ByteStringObject;
use CBOR\Decoder;
use CBOR\ListObject;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\StringStream;
use CBOR\TextStringObject;
use CBOR\UnsignedIntegerObject;
use EllipticCurve\Ecdsa;
use EllipticCurve\PrivateKey;
use EllipticCurve\PublicKey;
use EllipticCurve\Signature;
use Exception;

final class Sign1Message {

    private $protected;
    private $unprotected;
    private $payload;
    private $signature;

    /**
     * @param ByteStringObject $protected
     * @param MapObject $unprotected
     * @param ByteStringObject $payload
     */
    function __construct($protected, $unprotected, $payload)
    {
        $this->protected = $protected;
        $this->unprotected = $unprotected;
        $this->payload = $payload;
    }

    function _getSignatureStructure() {
        return ListObject::create()
            ->add(TextStringObject::create("Signature1"))
            ->add($this->protected)
            ->add($this->unprotected)
            ->add($this->payload);
    }

    function _computeSignature($key) {
        $signature_structure = $this->_getSignatureStructure();

        $this->signature = ByteStringObject::create(
            Ecdsa::sign($signature_structure->__toString(), $key)->toDer()
        );
        return $this->signature;
    }

    function encode($key){
        return ListObject::create()
            ->add($this->protected)
            ->add($this->unprotected)
            ->add($this->payload)
            ->add($this->_computeSignature($key))
            ->__toString();
    }

    function validate($key){        
        $signature_structure = $this->_getSignatureStructure();
        return Ecdsa::verify($signature_structure->__toString(), new Signature($this->signature->normalize()), $key);
    }

    /**
     * 
     * @return Sign1Message
     */
    static function decode($raw){
        $decoder = Decoder::create();

        $cborObj = $decoder->decode(StringStream::create($raw));

        if(!($cborObj instanceof ListObject)){
            throw new Exception("ListObject expected");
        }
        if($cborObj->count() !== 4){
            throw new Exception("La lista deberia contener cuatro elementos");
        }

        $msg = new self($cborObj->get(0), $cborObj->get(1), $cborObj->get(2));
        $msg->signature = $cborObj->get(3);

        return $msg;
    }
}