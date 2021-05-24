<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleador extends Model {
  use HasFactory;

  protected $connection = "galeno";

  protected $table = "AFEMPLEADORES";

  protected $primaryKey = 'ID';
  
  public $incrementing = false;

  public $timestamps = false;

  // In Laravel 6.0+ make sure to also set $keyType
  protected $keyType = 'string';

  protected $casts = [
    "FECHA_BAJA_EMP"=> "date"
  ];

  protected $hidden = [
    "ANO_FUNDEMPRESA_EMP",
    "CASILLA_EMP",
    "CODIGO_DEP",
    "CODIGO_FUNDEMPRESA_EMP",
    "CODIGO_MUN",
    "CODIGO_PROV",
    "DEPARTAMENTO_EMP",
    "DIRECCION_EMP",
    "ESTADO_EMP",
    "FECHA_BAJA_EMP",
    "FECHA_CONSTITUCION_EMP",
    "FECHA_INGRESO_EMP",
    "FECHA_INICIACION_ACTIVIDADES_EMP",
    "FECHA_REG_EMP",
    "ID",
    "ID_RAD",
    "ID_RGL",
    "ID_TAO",
    "ID_USO",
    "LOCALIDAD_EMP",
    "MAIL_EMP",
    "NOMBRE_EMP",
    "NUMERO_EMP",
    "NUMERO_NIT_EMP",
    "NUMERO_PATRONAL_EMP",
    "OBSERVACIONES_EMP",
    "REG_DATE",
    "REG_LOGIN",
    "REPRESENTANTE_LEGAL_EMP",
    "TELEFONO_EMP",
    "TELEFONO_REPRESENTANTE_EMP",
    "TIPO_EMP",
    "WEB_EMP",
    "ZONA_EMP"
  ];

  protected $appends = [
    "id",
    "numero_patronal",
    "nombre",
    "estado",
    "fecha_baja"
  ];

  function getIdAttribute(){
    return $this->attributes["ID"];
  }
  
  function getNumeroPatronalAttribute(){
    return $this->attributes["NUMERO_PATRONAL_EMP"];
  }

  function getNombreAttribute(){
    return $this->attributes["NOMBRE_EMP"];
  }

  function getEstadoAttribute(){
    return $this->attributes["ESTADO_EMP"];
  }

  function getFechaBajaAttribute(){
    return $this->attributes["FECHA_BAJA_EMP"];
  }

  function toArray(){
    $array = parent::toArray();
    $array["fecha_baja"] = $this->fecha_baja?->format("d/m/Y");
    return $array;
  }

  static function buscarPorIds($ids){
    return static::whereIn("ID", $ids)->get();
  }
  
  static function buscarPorId($id){
    return static::where("ID", $id)->first();
  }
}