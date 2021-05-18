<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function verTodo(User $user) {
      if($user->can(Permisos::VER_USUARIOS)) return true;
    }
    
    public function registrar(User $user, $payload) {
      if($user->can(Permisos::REGISTRAR_USUARIOS)) return true;
      if($user->can(Permisos::REGISTRAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO)
      && $user->regional_id == Arr::get($payload, "regional_id")) return true;
    }

    public function editar(User $user, $model, $payload) {
      if($user->username !== "admin"){
        if($user->can(Permisos::EDITAR_USUARIOS)) return true;
        if($user->can(Permisos::EDITAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO)
        && $user->regional_id == Arr::get($payload, "regional_id") && $user->role_id == $model->regional_id) return true;
      }
    }

    public function cambiarContrasena(User $user, $model, $payload) {
      if($user->can(Permisos::CAMBIAR_CONTRASEÑA)) return true;
      if($user->can(Permisos::CAMBIAR_CONTRASEÑA_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO) && 
      $user->regional_id == $model->regional_id) return true;
      if($user->id == $model->id && $model->validatePassword(Arr::get($payload, "old_password"))) return true;
    }

    public function enable(User $user, $model) {
      if($user->can(Permisos::CAMBIAR_CONTRASEÑA)) return true;
      if($user->can(Permisos::CAMBIAR_CONTRASEÑA_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO) && 
      $user->regional_id == $model->regional_id) return true;
      if($user->id == $model->id) return true;
    }

    public function disable(User $user, $model) {
      if($user->can(Permisos::BLOQUEAR_USUARIOS)) return true;
      if($user->can(Permisos::BLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO) && 
      $user->regional_id == $model->regional_id) return true;
      if($user->id == $model->id) return true;
    }
  }
