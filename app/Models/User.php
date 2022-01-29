<?php

namespace App\Models;

use App\Casts\CarnetIdentidad;
use App\Models\Traits\SaveToUpper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable, SaveToUpper;

    protected $guard_name = "sanctum";

    protected $with = ["roles"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ci',
        'apellido_paterno',
        'apellido_materno',
        'nombre',
        'username',
        'password',
        'estado',
        'regional_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "permissions",
        'ci_complemento',
        'password',
        'remember_token',
    ];

    protected $appends = [
        "nombre_completo"
    ];

    protected $casts = [
        "ci" => CarnetIdentidad::class,
        "created_at" =>  'date:d/m/Y',
        "updated_at" =>  'date:d/m/Y'
    ];

    function setPasswordAttribute($value)
    {
        $this->attributes["password"] = Hash::make($value);
    }

    function isSuperUser()
    {
        return $this->hasRole(1);//$this->hasRole("super user");
    }

    function validatePassword($password)
    {
        return Hash::check($password, $this->password);
    }

    function getNombreCompletoAttribute()
    {
        $nombreCompleto = $this->nombre;
        if ($this->apellido_materno)
            $nombreCompleto =  $this->apellido_materno . " " . $nombreCompleto;
        if ($this->apellido_paterno)
            $nombreCompleto =  $this->apellido_paterno . " " . $nombreCompleto;
        return $nombreCompleto;
    }

    function regional()
    {
        return $this->belongsTo(Regional::class);
    }
}
