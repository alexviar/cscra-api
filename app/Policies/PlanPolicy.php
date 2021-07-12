<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class PlanPolicy
{
    use HandlesAuthorization;

    public function verTodo(User $user, $filters){
        if($user->can(Permisos::VER_PLANES)) return true;
        if($user->can(Permisos::VER_PLANES_REGIONALES) && Arr::get($filters, "regional_id") == $user->regional_id) return true;
        if(Arr::get($filters, "creado_por") == $user->id) return true;
    }

    public function ver(User $user, $plan) {
        if($user->can(Permisos::VER_PLANES)) return true;
        if($user->can(Permisos::VER_PLANES_REGIONALES) && $plan->regional_id == $user->regional_id) return true;
        if($plan->usuario_id == $user->id) return true;
    }

    public function registrar(User $user, $payload) {
        if($user->can(Permisos::REGISTRAR_PLANES)) return true;
    }

    public function registrarAvance(User $user, $plan) {
        if($user->id == $plan->usuario_id) return true;
        return false;
    }


}
