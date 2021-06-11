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
  const VER_LISTA_DE_MORA = "Ver lista de mora";
  const VER_LISTA_DE_MORA_REGIONAL = "Ver lista de mora (regional)";
  const AGREGAR_EMPLEADOR_EN_MORA = "Agregar empleador en mora";
  const AGREGAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL = "Agregar empleador en mora (misma regional)";
  const QUITAR_EMPLEADOR_EN_MORA = "Quitar empleador en mora";
  const QUITAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL = "Quitar empleador en mora (misma regional)";

  /**
   * Medicos
   */
  const VER_MEDICOS = "Ver médicos";
  const VER_MEDICOS_REGIONAL = "Ver médicos (regional)";
  const REGISTRAR_MEDICOS = "Registrar médicos";
  const REGISTRAR_MEDICOS_REGIONAL = "Registrar médicos (regional)";
  const EDITAR_MEDICOS = "Editar médicos";
  const EDITAR_MEDICOS_REGIONAL = "Editar médicos (regional)";
  const BAJA_MEDICOS = "Baja médicos";
  const BAJA_MEDICOS_REGIONAL = "Baja médicos (regional)";

  /**
   * Proveedores
   */
  const VER_PROVEEDORES = "Ver proveedores";
  const VER_PROVEEDORES_REGIONAL = "Ver proveedores (regional)";
  const REGISTRAR_PROVEEDORES = "Registrar proveedores";
  const REGISTRAR_PROVEEDORES_REGIONAL = "Registrar proveedores (regional)";
  const EDITAR_PROVEEDORES = "Editar proveedores";
  const EDITAR_PROVEEDORES_REGIONAL = "Editar proveedores (regional)";
  const BAJA_PROVEEDOR = "Baja proveedores";
  const BAJA_PROVEEDOR_REGIONAL = "Baja proveedores (regional)";

  const VER_CONTRATOS_PROVEEDOR = "Ver contratos de proveedores";
  const VER_CONTRATOS_PROVEEDOR_REGIONAL = "Ver contratos de proveedores (regional)";
  const REGISTRAR_CONTRATO_PROVEEDOR = "Registrar contratos de proveedores";
  const REGISTRAR_CONTRATO_PROVEEDOR_REGIONAL = "Registrar contratos de proveedores (regional)";
  const CONSUMIR_CONTRATO_PROVEEDOR = "Consumir contratos de proveedores";
  const CONSUMIR_CONTRATO_PROVEEDOR_REGIONAL = "Consumir contratos de proveedores (regional)";
  const EXTENDER_CONTRATO_PROVEEDOR = "Extender contratos de proveedores";
  const EXTENDER_CONTRATO_PROVEEDOR_REGIONAL = "Extender contratos de proveedores (regional)";
  const ANULAR_CONTRATO_PROVEEDOR = "Anular contratos de proveedores";
  const ANULAR_CONTRATO_PROVEEDOR_REGIONAL = "Anular contratos de proveedores (regional)";

  /**
   * Especialidades
   */
  const VER_ESPECIALIDADES = "Ver especialidades";
  const REGISTRAR_ESPECIALIDADES = "Registrar especialidades";
  const EDITAR_ESPECIALIDADES = "Editar especialidades";
  const ELIMINAR_ESPECIALIDADES = "Eliminar especialidades";
  const IMPORTAR_ESPECIALIDADES = "Importar especialidades";

  /**
   * Prestaciones
   */
  const VER_PRESTACIONES = "Ver prestaciones";
  const REGISTRAR_PRESTACIONES = "Registrar prestaciones";
  const EDITAR_PRESTACIONES = "Editar prestaciones";
  const ELIMINAR_PRESTACIONES = "Eliminar prestaciones";
  const IMPORTAR_PRESTACIONES = "Importar prestaciones";
}