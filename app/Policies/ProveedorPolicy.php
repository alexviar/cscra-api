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
      if($user->can(Permisos::REGISTRAR_PROVEEDORES_REGIONAL) && $user->regional_id == Arr::get($payload, "general.regional_id")) return true;
    }

    public function actualizar(User $user, $proveedor, $payload) {
      if($user->can(Permisos::EDITAR_PROVEEDORES_REGIONAL)) return true;
      if($user->can(Permisos::EDITAR_PROVEEDORES_REGIONAL)
        && $user->regional_id == $proveedor->regional_id 
        && $user->regional_id == $payload["regional_id"]) return true;
    }

    // public function cambiarEstado(User $user, $medico){
    //   if($user->can(Permisos::BAJA_PROVEEDOR)) return true;
    //   if($user->can(Permisos::BAJA_PROVEEDOR_REGIONAL)
    //     && $user->regional_id == $medico->regional_id) return true;
    // }

    public function registrarContrato(User $user, $proveedor)
    {
        if($user->can(Permisos::REGISTRAR_CONTRATO_PROVEEDOR)) return true;
        if($user->can(Permisos::REGISTRAR_CONTRATO_PROVEEDOR_REGIONAL)
            && $user->regional_id == $proveedor->regional_id) return true;
    }
    
    public function consumirContrato(User $user, $proveedor)
    {
        if($user->can(Permisos::CONSUMIR_CONTRATO_PROVEEDOR)) return true;
        if($user->can(Permisos::CONSUMIR_CONTRATO_PROVEEDOR_REGIONAL)
            && $user->regional_id == $proveedor->regional_id) return true;
    }
    
    public function extenderContrato(User $user, $proveedor)
    {
        if($user->can(Permisos::EXTENDER_CONTRATO_PROVEEDOR)) return true;
        if($user->can(Permisos::EXTENDER_CONTRATO_PROVEEDOR_REGIONAL)
            && $user->regional_id == $proveedor->regional_id) return true;
    }
    
    public function anularContrato(User $user, $proveedor)
    {
        if($user->can(Permisos::ANULAR_CONTRATO_PROVEEDOR)) return true;
        if($user->can(Permisos::ANULAR_CONTRATO_PROVEEDOR_REGIONAL)
            && $user->regional_id == $proveedor->regional_id) return true;
    }
}
