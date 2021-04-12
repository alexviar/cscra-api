<?php

namespace App\Models;

use ArrayAccess;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * @property integer id
 * @property string numeroPatronal
 * @property string nombre
 */
class Empleador implements Arrayable, ArrayAccess {
  
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