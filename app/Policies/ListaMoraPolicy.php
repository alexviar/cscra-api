<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class ListaMoraPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function ver(User $user, $filter){
      if($user->can(Permisos::VER_LISTA_DE_MORA)) return true;
      if($user->can(Permisos::VER_LISTA_DE_MORA_REGIONAL) && Arr::get($filter, "regional_id") == $user->regional_id) return true;
    }

    public function agregar(User $user, $empleador) {
      if($user->can(Permisos::AGREGAR_EMPLEADOR_EN_MORA)) return true;
      if($user->can(Permisos::AGREGAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL) && $user->regional_id == $empleador->regional_local_id) return true;
    }

    public function quitar(User $user, $empleador) {
      if($user->can(Permisos::QUITAR_EMPLEADOR_EN_MORA)) return true;
      if($user->can(Permisos::QUITAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL) && $user->regional_id == $empleador->regional_local_id) return true;
    }
}
