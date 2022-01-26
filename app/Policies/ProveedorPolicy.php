<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class ProveedorPolicy
{
  use HandlesAuthorization;

  public function verTodo(User $user, $filter)
  {
    if ($user->hasPermissionTo(Permisos::VER_PROVEEDORES_REGIONAL)) return $user->regional_id == Arr::get($filter, "regional_id");
    if ($user->hasPermissionTo(Permisos::VER_PROVEEDORES)) return true;
  }

  public function ver(User $user, $proveedor)
  {
    if ($user->hasPermissionTo(Permisos::VER_PROVEEDORES_REGIONAL)) return $user->regional_id == $proveedor->regional_id;
    if ($user->hasPermissionTo(Permisos::VER_PROVEEDORES)) return true;
  }

  public function registrar(User $user, $payload)
  {
    if ($user->hasPermissionTo(Permisos::REGISTRAR_PROVEEDORES_REGIONAL)) return $user->regional_id == Arr::get($payload, "regional_id");
    if ($user->hasPermissionTo(Permisos::REGISTRAR_PROVEEDORES)) return true;
  }

  public function actualizar(User $user, $proveedor, $payload)
  {
    if ($user->hasPermissionTo(Permisos::ACTUALIZAR_PROVEEDORES_REGIONAL)) return $user->regional_id == $proveedor->regional_id && $user->regional_id == $payload["regional_id"];
    if ($user->hasPermissionTo(Permisos::ACTUALIZAR_PROVEEDORES)) return true;
  }

  public function actualizarEstado(User $user, $proveedor)
  {
    if ($user->hasPermissionTo(Permisos::ACTUALIZAR_ESTADO_PROVEEDORES_REGIONAL)) return $user->regional_id == $proveedor->regional_id;
    if ($user->hasPermissionTo(Permisos::ACTUALIZAR_ESTADO_PROVEEDORES)) return true;
  }
}
