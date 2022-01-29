<?php

namespace App\Policies;

use App\Models\Permisos;
use App\Models\SolicitudAtencionExterna;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class SolicitudAtencionExternaPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function verTodo(User $user, $filter)
    {
        // dd($user->hasPermissionTo(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL), Arr::get($filter, "regional_id") != $user->regional_id);
        if($user->hasPermissionTo(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL)) return $user->regional_id == Arr::get($filter, "regional_id");
        if($user->hasPermissionTo(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA)) return true;
    }

    // public function ver(User $user, SolicitudAtencionExterna $solicitud)
    // {
    //     if($user->hasPermissionTo(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL)) return $user->regional_id == $solicitud->regional_id;
    //     if($user->hasPermissionTo(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA)) return true;
    // }

    public function verDm11(User $user, SolicitudAtencionExterna $solicitud)
    {
        if ($user->hasPermissionTo(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL)) return $user->regional_id == $solicitud->regional_id;
        if ($user->hasPermissionTo(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA)) return true;
    }

    public function registrar(User $user, $payload)
    {
        if ($user->hasPermissionTo(Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL)) return $user->regional_id == Arr::get($payload, "regional_id");
        if ($user->hasPermissionTo(Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA)) return true;
    }
}
