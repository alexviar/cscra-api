<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrestacionPolicy
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

    public function verTodo(User $user){
      if($user->can(Permisos::VER_PRESTACIONES)) return true;
    }

    public function ver(User $user){
      if($user->can(Permisos::VER_PRESTACIONES)) return true;
    }

    public function registrar(User $user) {
      if($user->can(Permisos::REGISTRAR_PRESTACIONES)) return true;
    }

    public function editar(User $user, $model) {
      if($user->can(Permisos::EDITAR_PRESTACIONES)) return true;
    }

    public function eliminar(User $user) {
      if($user->can(Permisos::ELIMINAR_PRESTACIONES)) return true;
    }
}
