<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class ListaMoraPolicy
{
  use HandlesAuthorization;

  public function ver(User $user, $filter)
  {
    if ($user->hasPermissionTo(Permisos::VER_LISTA_DE_MORA_REGIONAL)) return $user->regional_id == Arr::get($filter, "regional_id");
    if ($user->hasPermissionTo(Permisos::VER_LISTA_DE_MORA)) return true;
  }

  public function agregar(User $user, $empleador)
  {
    if ($user->hasPermissionTo(Permisos::AGREGAR_A_LA_LISTA_DE_MORA_MISMA_REGIONAL)) return $user->regional_id == $empleador->regional_id;
    if ($user->hasPermissionTo(Permisos::AGREGAR_A_LA_LISTA_DE_MORA)) return true;
  }

  public function quitar(User $user, $empleador)
  {
    if ($user->hasPermissionTo(Permisos::QUITAR_DE_LA_LISTA_DE_MORA_MISMA_REGIONAL)) return $user->regional_id == $empleador->regional_id;
    if ($user->hasPermissionTo(Permisos::QUITAR_DE_LA_LISTA_DE_MORA)) return true;
  }
}
