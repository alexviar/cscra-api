<?php

namespace Database\Seeders;

use App\Models\Permisos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Permission::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // user permissions
        Permission::create(['name' => Permisos::VER_USUARIOS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::VER_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO, 'guard_name' => 'sanctum']);
        
        Permission::create(['name' => Permisos::REGISTRAR_USUARIOS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::REGISTRAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO, 'guard_name' => 'sanctum']);
        
        Permission::create(['name' => Permisos::EDITAR_USUARIOS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::EDITAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO, 'guard_name' => 'sanctum']);
        
        Permission::create(['name' => Permisos::BLOQUEAR_USUARIOS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::BLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO, 'guard_name' => 'sanctum']);
        
        Permission::create(['name' => Permisos::DESBLOQUEAR_USUARIOS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::DESBLOQUEAR_USUARIOS_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO, 'guard_name' => 'sanctum']);

        Permission::create(['name' => Permisos::CAMBIAR_CONTRASEÑA, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::CAMBIAR_CONTRASEÑA_DE_LA_MISMA_REGIONAL_QUE_EL_USUARIO, 'guard_name' => 'sanctum']);

        //rol permissions
        Permission::create(['name' => Permisos::VER_ROLES, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::REGISTRAR_ROLES, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::EDITAR_ROLES, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::ELIMINAR_ROLES, 'guard_name' => 'sanctum']);

        //atencion externa permissions
        Permission::create(['name' => Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::VER_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR, 'guard_name' => 'sanctum']);
        
        Permission::create(['name' => Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::REGISTRAR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL, 'guard_name' => 'sanctum']);

        Permission::create(['name' => Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_MISMA_REGIONAL, 'guard_name' => 'sanctum']);
        Permission::create(['name' => Permisos::EMITIR_SOLICITUDES_DE_ATENCION_EXTERNA_REGISTRADO_POR, 'guard_name' => 'sanctum']);

        //lista mora
        Permission::create(["name" => Permisos::VER_LISTA_DE_MORA, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::VER_LISTA_DE_MORA_REGIONAL, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::AGREGAR_EMPLEADOR_EN_MORA, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::AGREGAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::QUITAR_EMPLEADOR_EN_MORA, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::QUITAR_EMPLEADOR_EN_MORA_DE_LA_MISMA_REGIONAL, "guard_name" => "sanctum"]);

        //Medicos
        Permission::create(["name" => Permisos::VER_MEDICOS, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::VER_MEDICOS_REGIONAL, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::REGISTRAR_MEDICOS, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::REGISTRAR_MEDICOS_REGIONAL, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::EDITAR_MEDICOS, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::EDITAR_MEDICOS_REGIONAL, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::BAJA_MEDICOS, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::BAJA_MEDICOS_REGIONAL, "guard_name" => "sanctum"]);

        //Proveedores
        Permission::create(["name" => Permisos::VER_PROVEEDORES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::VER_PROVEEDORES_REGIONAL, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::REGISTRAR_PROVEEDORES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::REGISTRAR_PROVEEDORES_REGIONAL, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::EDITAR_PROVEEDORES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::EDITAR_PROVEEDORES_REGIONAL, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::BAJA_PROVEEDORES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::BAJA_PROVEEDORES_REGIONAL, "guard_name" => "sanctum"]);

        //Prestaciones
        Permission::create(["name" => Permisos::VER_PRESTACIONES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::REGISTRAR_PRESTACIONES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::EDITAR_PRESTACIONES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::ELIMINAR_PRESTACIONES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::IMPORTAR_PRESTACIONES, "guard_name" => "sanctum"]);

        //Prestaciones
        Permission::create(["name" => Permisos::VER_ESPECIALIDADES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::REGISTRAR_ESPECIALIDADES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::EDITAR_ESPECIALIDADES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::ELIMINAR_ESPECIALIDADES, "guard_name" => "sanctum"]);
        Permission::create(["name" => Permisos::IMPORTAR_ESPECIALIDADES, "guard_name" => "sanctum"]);
    }
}