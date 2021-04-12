<?php

namespace App\Models;

use ArrayAccess;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * @property integer id
 * @property string matricula
 * @property string apellido_paterno
 * @property string apellido_materno
 * @property string nombres
 * @property string estado
 * @property string fecha_extinsion
 */
class Asegurado implements Arrayable, ArrayAccess {

  private $attributes;

  function __construct($attributes=[])
  {
    if($attributes == NULL){
      throw new InvalidArgumentException();
    }
    $this->attributes = $attributes;
  }

  function __set($name, $value){
    $this->attributes[$name] = $value;
  }

  function __get($name){
    if($name == "partes_matricula") {
      return explode("-", $this->attributes["matricula"]);
    }
    if($name == "nombre_completo") {
      return trim("{$this->apellido_paterno} {$this->apellido_materno} {$this->nombres}");
    }
    return Arr::get($this->attributes, $name, null);
  }

  function toArray()
  {
    return $this->attributes;
  }
  
  function offsetExists($offset)
  {
    return Arr::has($this->attributes, $offset);
  }

  function offsetGet($offset)
  {
    return $this->attributes[$offset];
  }

  function offsetSet($offset, $value)
  {
    $this->attributes[$offset] = $value;
  }

  function offsetUnset($offset)
  {
    unset($this->attributes[$offset]);
  }


}