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

    public function verTodo(User $user, $filter)
    {
        // if($user->can(Permisos::VER_USUARIOS_DE_LA_MISMA_REGIONAL)) $grant = $user->regional_id == Arr::get($filter, "regional_id");
        // if(isset($grant)) return $grant; 
        // if($user->can(Permisos::VER_USUARIOS)) return true;
        if ($user->hasPermissionTo(Permisos::VER_USUARIOS_MISMA_REGIONAL)) return $user->regional_id == Arr::get($filter, "regional_id");
        if ($user->hasPermissionTo(Permisos::VER_USUARIOS)) return true;
    }

    public function ver(User $user, $model)
    {
        if ($user->hasPermissionTo(Permisos::VER_USUARIOS_MISMA_REGIONAL)) return $user->regional_id == $model->regional_id;
        if ($user->hasPermissionTo(Permisos::VER_USUARIOS)) return true;
    }

    public function registrar(User $user, $payload)
    {
        if ($user->hasPermissionTo(Permisos::REGISTRAR_USUARIOS_MISMA_REGIONAL)) return $user->regional_id == Arr::get($payload, "regional_id");
        if ($user->hasPermissionTo(Permisos::REGISTRAR_USUARIOS)) return true;
    }

    public function editar(User $user, $model, $payload)
    {
        if ($model->username === "admin") return false;
        if ($user->hasPermissionTo(Permisos::ACTUALIZAR_USUARIOS_MISMA_REGIONAL)) return $user->regional_id == Arr::get($payload, "regional_id") && $user->regional_id == $model->regional_id;
        if ($user->hasPermissionTo(Permisos::ACTUALIZAR_USUARIOS)) return true;
    }

    public function cambiarContrasena(User $user, $model, $payload)
    {
        if ($user->hasPermissionTo(Permisos::CAMBIAR_CONTRASEÑAS_MISMA_REGIONAL)) return $user->regional_id == $model->regional_id;
        if ($user->hasPermissionTo(Permisos::CAMBIAR_CONTRASEÑAS)) return true;
        if ($user->id == $model->id && Arr::has($payload, "old_password")) return true;
    }

    public function enable(User $user, $model)
    {
        if ($user->hasPermissionTo(Permisos::DESBLOQUEAR_USUARIOS_MISMA_REGIONAL)) return $user->regional_id == $model->regional_id;
        if ($user->hasPermissionTo(Permisos::DESBLOQUEAR_USUARIOS)) return true;
    }

    public function disable(User $user, $model)
    {
        if ($user->hasPermissionTo(Permisos::BLOQUEAR_USUARIOS_MISMA_REGIONAL)) return $user->regional_id == $model->regional_id;
        if ($user->hasPermissionTo(Permisos::BLOQUEAR_USUARIOS)) return true;
    }
}
