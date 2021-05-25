<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable;

    protected $guard_name = "sanctum";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'ci_raiz',
      'ci_complemento',
      'apellido_paterno',
      'apellido_materno',
      'nombres',
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
        'password',
        'remember_token',
    ];

    protected $appends = [
      "nombreCompleto",
      "ci",
      "all_permissions"
    ];

    protected $casts = [
      "created_at" =>  'date: d/m/Y h:i:m',
      "updated_at" =>  'date: d/m/Y h:i:m',
      "estado" => 'boolean'
    ];

    // /**
    //  * The attributes that should be cast to native types.
    //  *
    //  * @var array
    //  */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    // function setPasswordAttribute($value){
    //   $this->setAttribute("password", Hash::make($value));
    // }

    function validatePassword($password) {
      return Hash::check($password);
    }

    function getAllPermissionsAttribute(): Collection
    {
      return $this->getAllPermissions();
    }

    function getCiAttribute(){
      return $this->ci_raiz . ($this->ci_complemento ? " " .  $this->ci_complemento :  "");
    }

    function getNombreCompletoAttribute(){
      $nombreCompleto = $this->nombres;
      if($this->apellido_materno)
        $nombreCompleto =  $this->apellido_materno . " " . $nombreCompleto;
      if($this->apellido_paterno)
        $nombreCompleto =  $this->apellido_paterno . " " . $nombreCompleto;
      return $nombreCompleto;        
    }

    function getRoleNamesAttribute(){
      return $this->getRoleNames();
    }


}
