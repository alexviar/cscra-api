<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class PlanPolicy
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

    public function verTodo(User $user, $filters){
        // if($user->can(Permisos::VER_PLANES)) return true;
        // if($user->can(Permisos::VER_PLANES_REGIONALES) && Arr::get($filters, "regional_id") == $user->regional_id) return true;
        // if($user->can(Permisos::VER_PLANES_PROPIOS) && Arr::get($filters, "usuario_id") == $user->id) return true;
    }

    public function registrar(User $user, $payload) {
        // if($user->can(Permisos::GESTIONAR_PLANES)) return true;
        // if($user->can(Permisos::GESTIONAR_PLANES_REGIONALES)) return true;
    }
}
