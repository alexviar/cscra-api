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
        // dd($user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL), Arr::get($filter, "regional_id") != $user->regional_id);
        if ($user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA)) return true;
        if (($user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR) || $user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL))
            && (!$user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR) || Arr::get($filter, "registrado_por_id") == $user->id)
            && (!$user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL) || Arr::get($filter, "regional_id") == $user->regional_id)
        ) return true;
    }

    public function ver(User $user, SolicitudAtencionExterna $solicitud, $signature)
    {
        if ($user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA)) return true;
        if (($user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR) || $user->can(Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL))
        && (!$user->can(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR) || $solicitud->usuario_id == $user->id)
        && (!$user->can(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL) || $solicitud->regional_id == $user->regional_id)
        ) return true;
        if ($solicitud->validateSignature($signature)) return true;
    }

    public function verDm11(User $user, SolicitudAtencionExterna $solicitud)
    {
        if ($user->can(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA)) return true;
        if (($user->can(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR) || $user->can(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL))
            && (!$user->can(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR) || $solicitud->usuario_id == $user->id)
            && (!$user->can(Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL) || $solicitud->regional_id == $user->regional_id)
        ) return true;
    }

    public function registrar(User $user, $payload)
    {
        if ($user->can(Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA)) return true;
        if (
            $user->can(Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL)
            && Arr::get($payload, "regional_id") == $user->regional_id
        ) return true;
    }
}
