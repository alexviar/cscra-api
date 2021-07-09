<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avance extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "avances";

    protected $casts = [
        "fecha" => "date:Y-m-d",
    ];

    protected $appends = [
        "informe_url"
    ];

    function getInformeUrlAttribute(){
        return route("seguimiento.informes", [
            "id" => str_pad($this->id, 10, '0', STR_PAD_LEFT)
        ]);
    }

}
