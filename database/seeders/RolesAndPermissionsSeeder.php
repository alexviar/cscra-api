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

        // //especialidades permissions
        // Permission::create(['name' => 'ver especialidades', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'registrar especialidades', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'editar especialidades', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'eliminar especialidades', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'importar especialidades', 'guard_name' => 'sanctum']);

        // //prestaciones premissions
        // Permission::create(['name' => 'ver prestaciones', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'registrar prestaciones', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'editar prestaciones', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'eliminar prestaciones', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'importar prestaciones', 'guard_name' => 'sanctum']);

        // //medicos premissions
        // Permission::create(['name' => 'ver medicos', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'registrar medicos', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'editar medicos', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'bloquear medicos', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'desbloquear medicos', 'guard_name' => 'sanctum']);

        // //proveedores premissions
        // Permission::create(['name' => 'ver proveedores', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'registrar proveedores', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'editar proveedores', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'bloquear proveedores', 'guard_name' => 'sanctum']);
        // Permission::create(['name' => 'desbloquear proveedores', 'guard_name' => 'sanctum']);
    }
}