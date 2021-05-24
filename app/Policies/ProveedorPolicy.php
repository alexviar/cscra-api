<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class ProveedorPolicy
{
    use HandlesAuthorization;

    public function verTodo(User $user, $filter){
      if($user->can(Permisos::VER_PROVEEDORES_REGIONAL)) return true;
      if($user->can(Permisos::VER_PROVEEDORES_REGIONAL) && Arr::get($filter, "regional_id") == $user->regional_id) return true;
    }

    public function ver(User $user, $proveedor){
      if($user->can(Permisos::VER_PROVEEDORES_REGIONAL)) return true;
      if($user->can(Permisos::VER_PROVEEDORES_REGIONAL) && $proveedor->regional_id == $user->regional_id) return true;
    }

    public function registrar(User $user, $payload) {
      if($user->can(Permisos::REGISTRAR_PROVEEDORES)) return true;
      if($user->can(Permisos::REGISTRAR_PROVEEDORES_REGIONAL) && $user->regional_id == $payload["regional_id"]) return true;
    }

    public function actualizar(User $user, $medico, $payload) {
      if($user->can(Permisos::EDITAR_PROVEEDORES_REGIONAL)) return true;
      if($user->can(Permisos::EDITAR_PROVEEDORES_REGIONAL)
        && $user->regional_id == $medico->regional_id 
        && Arr::has($payload, "regional_id") && $user->regional_id == $payload["regional_id"]) return true;
    }

    public function cambiarEstado(User $user, $medico){
      if($user->can(Permisos::BAJA_PROVEEDORES_REGIONAL)) return true;
      if($user->can(Permisos::BAJA_PROVEEDORES_REGIONAL)
        && $user->regional_id == $medico->regional_id) return true;
    }
}