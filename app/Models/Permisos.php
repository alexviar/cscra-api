<?php

namespace App\Models;

use ReflectionClass;

class Permisos
{

    /**
     * Permisos de solicitud de transferencia externa
     */
    const VER_SOLICITUDES_DE_ATENCION_EXTERNA = "Ver solicitudes de atención externa";
    const VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL = "Ver solicitudes de atención externa (misma regional)";
    const REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA = "Registrar solicitudes de atención externa";
    const REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL = "Registrar solicitudes de atención externa (misma regional)";
    const EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA = "Emitir solicitudes de atención externa";
    const EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL = "Emitir solicitudes de atención externa (misma regional)";

    /**
     * Permisos de usuarios
     */
    const VER_USUARIOS = "Ver usuarios";
    const VER_USUARIOS_MISMA_REGIONAL = "Ver usuarios (misma regional)";
    const REGISTRAR_USUARIOS = "Registrar usuarios";
    const REGISTRAR_USUARIOS_MISMA_REGIONAL = "Registrar usuarios (misma regional)";
    const ACTUALIZAR_USUARIOS = "Actualizar usuarios";
    const ACTUALIZAR_USUARIOS_MISMA_REGIONAL = "Actualizar usuarios (misma regional)";
    const BLOQUEAR_USUARIOS = "Bloquear usuarios";
    const BLOQUEAR_USUARIOS_MISMA_REGIONAL = "Bloquear usuarios (misma regional)";
    const DESBLOQUEAR_USUARIOS = "Desbloquear usuarios";
    const DESBLOQUEAR_USUARIOS_MISMA_REGIONAL = "Desbloquear usuarios (misma regional)";
    const CAMBIAR_CONTRASEÑAS = "Cambiar contraseñas";
    const CAMBIAR_CONTRASEÑAS_MISMA_REGIONAL = "Cambiar contraseñas (misma regional)";

    /**
     * Permisos de roles
     */
    const VER_ROLES = "Ver roles";
    const REGISTRAR_ROLES = "Registrar roles";
    const ACTUALIZAR_ROLES = "Actualizar roles";
    const ELIMINAR_ROLES = "Eliminar roles";

    /**
     * Permisos de lista de mora
     */
    const VER_LISTA_DE_MORA = "Ver la lista de mora";
    const VER_LISTA_DE_MORA_REGIONAL = "Ver la lista de mora (misma regional)";
    const AGREGAR_A_LA_LISTA_DE_MORA = "Agregar a la lista de mora";
    const AGREGAR_A_LA_LISTA_DE_MORA_MISMA_REGIONAL = "Agregar a la lista de mora (misma regional)";
    const QUITAR_DE_LA_LISTA_DE_MORA = "Quitar de la lista de mora";
    const QUITAR_DE_LA_LISTA_DE_MORA_MISMA_REGIONAL = "Quitar de la lista de mora (misma regional)";

    /**
     * Medicos
     */
    const VER_MEDICOS = "Ver médicos";
    const VER_MEDICOS_REGIONAL = "Ver médicos (misma regional)";
    const REGISTRAR_MEDICOS = "Registrar médicos";
    const REGISTRAR_MEDICOS_REGIONAL = "Registrar médicos (misma regional)";
    const ACTUALIZAR_MEDICOS = "Actualizar médicos";
    const ACTUALIZAR_MEDICOS_REGIONAL = "Actualizar médicos (misma regional)";
    const ACTUALIZAR_ESTADO_MEDICOS = "Actualizar el estado de los médicos";
    const ACTUALIZAR_ESTADO_MEDICOS_REGIONAL = "Actualizar el estado de los médicos (misma regional)";

    /**
     * Proveedores
     */
    const VER_PROVEEDORES = "Ver proveedores";
    const VER_PROVEEDORES_REGIONAL = "Ver proveedores (misma regional)";
    const REGISTRAR_PROVEEDORES = "Registrar proveedores";
    const REGISTRAR_PROVEEDORES_REGIONAL = "Registrar proveedores (misma regional)";
    const ACTUALIZAR_PROVEEDORES = "Actualizar proveedores";
    const ACTUALIZAR_PROVEEDORES_REGIONAL = "Actualizar proveedores (misma regional)";
    const ACTUALIZAR_ESTADO_PROVEEDORES = "Actualizar el estado de los proveedores";
    const ACTUALIZAR_ESTADO_PROVEEDORES_REGIONAL = "Actualizar el estado de los proveedores (misma regional)";

    static function toArray()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
