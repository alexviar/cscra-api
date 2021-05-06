<?php

namespace App\Models\Galeno;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Afiliado extends Model {

  protected $connection = "galeno";

  protected $table = "AFAFILIADOS";

  protected $casts = [
    "fecha_extinsion"=>"date: Y-m-d",
    "fecha_validez_seguro"=>"date: Y-m-d",
  ];

  protected $hidden = [
    "FOTO_AFI",
    "CARNET",
    "CARNET_EXP",
    "DEP_NACIMIENTO_AFI",
    "DIRECCION_AFI",
    "ESTADO_AFI",
    "ESTADO_CIVIL_AFI",
    "FECHA_NACIMIENTO_AFI",
    "FOJAS",
    "FOTO",
    "ID",
    "IDSIRA",
    "ID_RGL",
    "LUGAR_NACIMIENTO_AFI",
    "MATERNO_AFI",
    "MATRICULA",
    "MATRICULA_CO",
    "MOVIL_AFI",
    "NOMBRE_AFI",
    "NUM_AFILIADO_AFI",
    "OBSERVACIONES",
    "OCUPACION_AFI",
    "PATERNO_AFI",
    "REG_DATE",
    "REG_LOGIN",
    "SEXO_AFI",
    "TELEFONO_AFI",
    "TIPO_AFI",
    "TIPO_SANGRE_AFI",
    "afiliacionesComoTitular",
    "afiliacionesComoBeneficiario"
  ];

  protected $appends = [
    "id",
    "matricula",
    "matricula_complemento",
    "apellidoPaterno",
    "apellidoMaterno",
    "nombres",
    "estado",
    "fecha_validez_seguro",
    "fecha_extinsion",
    "tipo",
    "empleador",
    "afiliacionDelTitular",
    "ultimaAfiliacion"
  ];

  protected $primaryKey = 'ID';
  
  public $incrementing = false;

  // In Laravel 6.0+ make sure to also set $keyType
  protected $keyType = 'string';

  function getIdAttribute(){
    return $this->attributes["ID"];
  }

  function getMatriculaAttribute(){
    return $this->attributes["MATRICULA"];// .  "-".$this->attributes["MATRICULA_CO"];
  }

  function getMatriculaComplementoAttribute(){
    return $this->getAttribute("MATRICULA_CO");
  }

  function getNombreCompletoAttribute(){
    $nombreCompleto = $this->nombres;
    if($this->apellido_materno){
      $nombreCompleto = $this->apellido_materno . " " . $nombreCompleto;
    }
    if($this->apellido_paterno){
      $nombreCompleto = $this->apellido_paterno . " " . $nombreCompleto;
    }
    return $nombreCompleto;
  }

  function getApellidoPaternoAttribute(){
    return $this->PATERNO_AFI;
  }

  function getApellidoMaternoAttribute(){
    return $this->MATERNO_AFI;
  }

  function getNombresAttribute(){
    return $this->NOMBRE_AFI;
  }

  function getEstadoAttribute(){
    return $this->ESTADO_AFI;
  }

  function getTipoAttribute(){
    return $this->TIPO_AFI;
  }

  function getEmpleadorAttribute(){
    return $this->ultimaAfiliacion?->empleador;
  }

  function getAfiliacionDelTitularAttribute(){
    if($this->tipo == 2){
      return $this->ultimaAfiliacion?->afiliacionDelTitular;
    }
  }
  
  function getFechaExtinsionAttribute(){
    if($this->tipo == 2){
      return $this->ultimaAfiliacion?->fecha_extinsion;
    }
  }

  function getFechaValidezSeguroAttribute(){
    return $this->ultimaAfiliacion?->baja?->fecha_validez_seguro;
  }

  function getUltimaAfiliacionAttribute(){
    // Log::debug(json_encode([$this->afiliacionesComoBeneficiario, $this->afiliacionesComoTitular]));
    if($this->tipo == 1){
      return $this->afiliacionesComoTitular->sortByDesc(fn ($afi) => $afi->ID)->first();
    }
    else if($this->tipo == 2){
      return $this->afiliacionesComoBeneficiario->sortByDesc(fn ($afi) => $afi->ID)->first();
    }
  }

  // function titular(){
  //   $obj = new stdClass;
  //   $obj->id = $this->afiliacionDelTitular->afiliado->id;
  //   $obj->estado = $this->afiliacion
  // }

  function afiliacionesComoTitular(){
    return $this->hasMany(AfiliacionTitular::class, "ID_AFO", "ID");
  }

  function afiliacionesComoBeneficiario(){
    return $this->hasMany(AfiliacionBeneficiario::class, "ID_AFO", "ID");
  }

  function toArray(){
    $this->makeHidden(["afiliacionDelTitular", "fechaValidezSeguro", "fechaExtinsion"]);
    $array = parent::toArray();
    $array["fecha_validez_seguro"] = $this->fechaValidezSeguro?->format("d/m/Y");
    $array["fecha_extinsion"] = $this->fechaExtinsion?->format("d/m/Y");
    $array["titular"] = $this->afiliacionDelTitular ? [
      "id"  => $this->afiliacionDelTitular->afiliado->id,
      "estado"  => $this->afiliacionDelTitular->estado,
      "fecha_validez_seguro" => $this->afiliacionDelTitular->fecha_validez_seguro?->format("d/m/Y"),
      "matricula" => $this->afiliacionDelTitular->afiliado->matricula,
      "apellido_paterno" => $this->afiliacionDelTitular->afiliado->apellido_paterno,
      "apellido_materno" => $this->afiliacionDelTitular->afiliado->apellido_materno,
      "nombres" => $this->afiliacionDelTitular->afiliado->nombres,
    ] : null;
    return $array;
  }

  static function buscarPorId($id){
    return static::find($id);
  }

  static function buscarPorIds($ids){
    return static::whereIn("ID", $ids)->get();
  }
}