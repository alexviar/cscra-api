<?php

namespace App\Models\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class CarnetIdentidad implements Arrayable {

    public $raiz;
    public $complemento;

    function __construct($raiz, $complemento)
    {
        $this->raiz = $raiz;
        $this->complemento = $complemento;   
    }

    function __toString()
    {
        $texto = $this->raiz."";
        if($this->complemento) $texto .= "-".$this->complemento;
        return $texto;
    }

    function toArray()
    {
        return [
            "raiz" => $this->raiz,
            "complemento" => $this->complemento,
            "texto" => $this->__toString()
        ];
    }
}