<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class MedicoPolicy
{
    use HandlesAuthorization;

    public function verTodo(User $user, $filter){
      if($user->can(Permisos::VER_MEDICOS_REGIONAL)) return true;
      if($user->can(Permisos::VER_MEDICOS_REGIONAL) && Arr::get($filter, "regional_id") == $user->regional_id) return true;
    }

    public function registrar(User $user, $payload) {
      if($user->can(Permisos::REGISTRAR_MEDICOS)) return true;
      if($user->can(Permisos::REGISTRAR_MEDICOS_REGIONAL) && $user->regional_id == $payload["regional_id"]) return true;
    }

    public function editar(User $user, $medico, $payload) {
      if($user->can(Permisos::EDITAR_MEDICOS_REGIONAL)) return true;
      if($user->can(Permisos::EDITAR_MEDICOS_REGIONAL)
        && $user->regional_id == $medico->regional_id 
        && Arr::has($payload, "regional_id") && $user->regional_id == $payload["regional_id"]) return true;
    }

    public function cambiarEstado(User $user, $medico){
      if($user->can(Permisos::BAJA_MEDICOS_REGIONAL)) return true;
      if($user->can(Permisos::BAJA_MEDICOS_REGIONAL)
        && $user->regional_id == $medico->regional_id) return true;
    }
}
