<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoProveedor extends Model{
  public $timestamps = false;

  protected $table = "contratos_proveedores";

  protected $fillable = ["proveedor_id", "inicio", "fin", "modalidad_pago", "tipo_contrato"];

  function prestaciones(){
    return $this->belongsToMany(Prestacion::class, "prestaciones_contratadas", "contrato_id", "prestacion_id");
  }
}