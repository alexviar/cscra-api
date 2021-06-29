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

    public function verTodo(User $user, $filter) {
      if($user->can(Permisos::VER_USUARIOS)) return true;
      if($user->can(Permisos::VER_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO)
      && $user->regional_id == Arr::get($filter, "regional_id")) return true;
    }

    public function ver(User $user, $model) {
        if($user->can(Permisos::VER_USUARIOS)) return true;
        if($user->can(Permisos::VER_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO)
        && $user->regional_id == $model->regional_id) return true;
     }
    
    public function registrar(User $user, $payload) {
      if($user->can(Permisos::REGISTRAR_USUARIOS)) return true;
      if($user->can(Permisos::REGISTRAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO)
      && $user->regional_id == Arr::get($payload, "regional_id")) return true;
    }

    public function editar(User $user, $model, $payload) {
        if($model->username === "admin") return false;
        if($user->can(Permisos::EDITAR_USUARIOS)) return true;
        if($user->can(Permisos::EDITAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO)
        && $user->regional_id == Arr::get($payload, "regional_id") && $user->regional_id == $model->regional_id) return true;
    }

    public function cambiarContrasena(User $user, $model, $payload) {
      if($user->can(Permisos::CAMBIAR_CONTRASEÑA)) return true;
      if($user->can(Permisos::CAMBIAR_CONTRASEÑA_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO) && 
      $user->regional_id == $model->regional_id) return true;
      if($user->id == $model->id/*&& $model->validatePassword(Arr::get($payload, "old_password"))*/) return true;
    }

    public function enable(User $user, $model) {
      if($user->can(Permisos::DESBLOQUEAR_USUARIOS)) return true;
      if($user->can(Permisos::DESBLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO) && 
      $user->regional_id == $model->regional_id) return true;
    }

    public function disable(User $user, $model) {
      if($user->can(Permisos::BLOQUEAR_USUARIOS)) return true;
      if($user->can(Permisos::BLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO) && 
      $user->regional_id == $model->regional_id) return true;
    }
  }
