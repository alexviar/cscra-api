<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudAtencionExterna extends Model {

  public $timestamps = false;

  protected $table = "atenciones_externas";

  function getNumeroAttribute(){
    return str_pad($this->id, 10, '0', STR_PAD_LEFT);
  }

  function medico(){
    return $this->belongsTo(Medico::class, "medico_id");
  }

  function proveedor(){
    return $this->belongsTo(Proveedor::class, "proveedor_id");
  }

  function regional(){
    return $this->belongsTo(Regional::class, "regional_id");
  }

  function prestacionesSolicitadas(){
    return $this->hasMany(PrestacionSolicitada::class, "transferencia_id");
  }
}