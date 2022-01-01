<?php

namespace App\Models;

use App\Models\Galeno\Empleador;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaMoraItem extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = "lista_mora";

    protected $fillable = [
        "empleador_id",
        "numero_patronal",
        "nombre",
        "regional_id"
    ];

    // protected $appends = [
    //   "numero_patronal_empleador",
    //   "nombre_empleador"
    // ];

    // function getNumeroPatronalEmpleadorAttribute(){
    //   return $this->empleador->numero_patronal;
    // }

    // function getNombreEmpleadorAttribute(){
    //   return $this->empleador->nombre;
    // }

    function empleador(){
      return $this->belongsTo(Empleador::class, "empleador_id", "ID");
    }

    static function buscarPorIdEmpleadores($idEmpleadores)
    {
        return static::whereIn("empleador_id", $idEmpleadores)->get();
    }

    static function buscarPorIdEmpleador($idEmpleador)
    {
        return static::where("empleador_id", $idEmpleador)->first();
    }
}
