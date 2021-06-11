<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    public $timestamps = false;
    protected $table = "provincias";

    function departamento()
    {
        return $this->belongsTo(Departamento::class, "departamento_id", "id");
    }
}
