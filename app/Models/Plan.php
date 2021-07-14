<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "planes";

    protected $fillable = [
        "objetivo_general",
        "regional_id",
        "area_id",
        "usuario_id"
    ];

    protected $with = [
        "actividades",
        "regional",
        "area"
    ];

    protected $appends = [
        "avance",
        "avance_esperado"
    ];

    function getAvanceAttribute() {
        return $this->actividades->where("avance", 100)->count();
    }

    function getAvanceEsperadoAttribute() {

        return $this->actividades->where("avance_esperado", 100)->count();
    }

    function actividades() {
        return $this->hasMany(Actividad::class, "plan_id", "id");
    }

    function regional() {
        return $this->belongsTo(Regional::class, "regional_id", "id");
    }

    function area() {
        return $this->belongsTo(Area::class, "area_id", "id");
    }
}
