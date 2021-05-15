<?php

namespace App\Models;

class Permisos {

  /**
   * Permisos de solicitud de transferencia externa
   */
  const VER_SOLICITUDES_DE_ATENCION_EXTERNA = "Ver solicitudes de atencion externa";
  const VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL = "Ver solicitudes de atencion externa (misma regional)";
  const VER_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR = "Ver solicitudes de atencion externa (registrado por)";
  const REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA = "Registrar solicitudes de atencion externa";
  const REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL = "Registrar solicitudes de atencion externa (misma regional)";
  // const REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR = "Registrar solicitudes de atencion externa (registrado por)";
  const EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA = "Emitir solicitudes de atencion externa";
  const EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL = "Emitir solicitudes de atencion externa (misma regional)";
  const EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR = "Emitir solicitudes de atencion externa (registrado por)";
  
  /**
   * Permisos de usuarios
   */
  const VER_USUARIOS = "Ver usuarios";
  const VER_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO = "Ver usuarios (misma regional)";
  const REGISTRAR_USUARIOS = "Registrar usuarios";
  const REGISTRAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO = "Registrar usuarios (misma regional)";
  const EDITAR_USUARIOS = "Editar usuarios";
  const EDITAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO = "Editar usuarios (misma regional)";
  const BLOQUEAR_USUARIOS = "Bloquear usuarios";
  const BLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO = "Bloquear usuarios (misma regional)";
  const DESBLOQUEAR_USUARIOS = "Desbloquear usuarios";
  const DESBLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO = "Desbloquear usuarios (misma regional)";
  const CAMBIAR_CONTRASEÑA = "Cambiar contraseña";
  const CAMBIAR_CONTRASEÑA_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO = "Cambiar contraseña (misma regional)";

  /**
   * Permisos de roles
   */
  const VER_ROLES = "Ver roles";
  const REGISTRAR_ROLES = "Registrar roles";
  const EDITAR_ROLES = "Editar roles";
  const ELIMINAR_ROLES = "Eliminar roles";

  /**
   * Permisos de lista de mora
   */

  const AGREGAR_EMPLEADOR_EN_MORA = "Agregar empleador en mora";
  const AGREGAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL = "Agregar empleador en mora de la misma regional";
  const QUITAR_EMPLEADOR_EN_MORA = "Quitar empleador en mora";
  const QUITAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL = "Quitar empleador en mora de la misma regional";

}