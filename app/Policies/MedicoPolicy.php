<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class MedicoPolicy
{
  use HandlesAuthorization;

  public function verTodo(User $user, $filter)
  {
    if ($user->hasPermissionTo(Permisos::VER_MEDICOS_REGIONAL)) return $user->regional_id == Arr::get($filter, "regional_id");
    if ($user->hasPermissionTo(Permisos::VER_MEDICOS)) return true;
  }

  public function ver(User $user, $medico)
  {
    if ($user->hasPermissionTo(Permisos::VER_MEDICOS_REGIONAL)) return $user->regional_id == $medico->regional_id;
    if ($user->hasPermissionTo(Permisos::VER_MEDICOS)) return true;
  }

  public function registrar(User $user, $payload)
  {
    if ($user->hasPermissionTo(Permisos::REGISTRAR_MEDICOS_REGIONAL)) return $user->regional_id == $payload["regional_id"];
    if ($user->hasPermissionTo(Permisos::REGISTRAR_MEDICOS)) return true;
  }

  public function editar(User $user, $medico, $payload)
  {
    if ($user->hasPermissionTo(Permisos::ACTUALIZAR_MEDICOS_REGIONAL)) return $user->regional_id == $medico->regional_id && $user->regional_id == $payload["regional_id"];
    if ($user->hasPermissionTo(Permisos::ACTUALIZAR_MEDICOS)) return true;
  }

  public function cambiarEstado(User $user, $medico)
  {
    if ($user->hasPermissionTo(Permisos::ACTUALIZAR_ESTADO_MEDICOS_REGIONAL)) return $user->regional_id == $medico->regional_id;
    if ($user->hasPermissionTo(Permisos::ACTUALIZAR_ESTADO_MEDICOS)) return true;
  }
}
