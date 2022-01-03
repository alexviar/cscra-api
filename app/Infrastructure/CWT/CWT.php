<?php

namespace App\Infrastructure\CWT;

use CBOR\AbstractCBORObject;
use CBOR\ByteStringObject;
use CBOR\CBORObject;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\Normalizable;
use CBOR\UnsignedIntegerObject;
use Exception;

final class CWT {

    const ISS_KEY = 1;
    const SUB_KEY = 2;
    const AUD_KEY = 3;
    const EXP_KEY = 4;
    const IAT_KEY = 6;

    // private MapObject $map;

    // function __construct()
    // {
    //     $map = MapObject::create();
    // }

    // function setIssuer($iss) {
    //     $this->map->add(UnsignedIntegerObject::create(self::ISS_KEY), $iss);
    //     return $this;
    // }

    // function setAudience($aud) {
    //     $this->map->add(UnsignedIntegerObject::create(self::AUD_KEY), $aud);
    //     return $this;
    // }

    // function setSubject($sub){
    //     $this->map->add(UnsignedIntegerObject::create(self::SUB_KEY), $sub);
    //     return $this;
    // }

    // function setExpiration($exp){
    //     $this->map->add(UnsignedIntegerObject::create(self::EXP_KEY), $exp);
    //     return $this;
    // }

    // function setIssuedAt($iat){        
    //     $this->map->add(UnsignedIntegerObject::create(self::IAT_KEY), $iat);
    //     return $this;
    // }

    // /**
    //  * @param UnsignedIntegerObject|NegativeIntegerObject $key 
    //  */
    // function addCustomClaim($key, $value){  
    //     $normalizedKey = $key->normalize();
    //     if($normalizedKey >= 0 && $normalizedKey <= 7) {
    //         throw new Exception("Key '{$normalizedKey}' is used for registered claims");
    //     }  
    //     $this->map->add($key, $value);
    //     return $this;
    // }

    // function encode($key){
    //     $this->msg = new Sign1Message(
    //         ByteStringObject::create(
    //             MapObject::create()
    //                 ->add(UnsignedIntegerObject::create(COSEHeaders::ALGORITHM), UnsignedIntegerObject::create(-7))
    //                 ->__toString()
    //         ),
    //         MapObject::create(),
    //         ByteStringObject::create($this->map->__toString()), $key);

    //     return $this->msg->encode();
    // }

    // function validate($key){
    //     $this->msg->validate($key);
    // }

    // static function parse($raw) {
    //     $cwt = new self();
    //     $msg = Sign1Message::decode($raw);
    //     $cwt->msg = $msg;
    //     return $cwt;
    // }


}