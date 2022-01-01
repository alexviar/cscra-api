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
      "nombre_completo",
      "ci",
      "estado_text",
      "all_permissions"
    ];

    protected $casts = [
      "created_at" =>  'date:Y-m-d',
      "updated_at" =>  'date:Y-m-d'
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

    function isSuperUser()
    {
        return $this->hasRole("super user");
    }

    function validatePassword($password) {
      return Hash::check($password, $this->password);
    }

    function getAllPermissionsAttribute(): Collection
    {
      return $this->getAllPermissions();
    }

    function getCiAttribute(){
      return $this->ci_raiz . ($this->ci_complemento ? " " .  $this->ci_complemento :  "");
    }

    function getEstadoTextAttribute(){
        return $this->estado == 1 ? "Activo" : ($this->estado == 2 ? "Bloqueado" : null);
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
