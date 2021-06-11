<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    public $timestamps = false;
    protected $table = "municipios";

    function provincia()
    {
        return $this->belongsTo(Provincia::class, "provincia_id", "id");
    }
}
