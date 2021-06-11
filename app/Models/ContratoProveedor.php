<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoProveedor extends Model
{
    use HasFactory;

    const CONSUMIDO = 2;
    const ANULADO = 3;

    public $timestamps = false;

    protected $table = "contratos_proveedores";

    protected $fillable = ["proveedor_id", "inicio", "fin"];

    protected $appends = ["estado_text"];

    protected $casts = [
        "inicio" => "date:Y-m-d",
        "fin" => "date:Y-m-d",
        "extension" => "date:Y-m-d"
    ];

    function getEstadoTextAttribute()
    {
        $now = Carbon::now();
        if ($this->estado == 3) return "Anulado";
        if ($this->fin && $now->gt($this->fin)) {
            return "Finalizado";
        }
        if ($this->extension) {
            if ($now->gt($this->extension)) {
                return $this->estado == 2 ? "Consumido" : "Finalizado";
            }
            return "Extendido";
        }
        if ($this->estado == 2) return "Consumido";
        return "Activo";
    }

    function getConsumidoAttribute()
    {
        return $this->estado == 2;
    }

    function prestaciones()
    {
        return $this->belongsToMany(Prestacion::class, "prestaciones_contratadas", "contrato_id", "prestacion_id");
    }

    function proveedor()
    {
        return $this->belongsTo(Proveedor::class, "proveedor_id", "id");
    }
}
