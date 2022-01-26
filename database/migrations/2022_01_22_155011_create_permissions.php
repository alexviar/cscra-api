<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class CreatePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // user permissions
        Permission::create(['name' => "Ver usuarios", 'guard_name' => 'sanctum', 'tag' => 1]);
        Permission::create(['name' => "Ver usuarios (misma regional)", 'guard_name' => 'sanctum', 'tag' => 1]);
        
        Permission::create(['name' => "Registrar usuarios", 'guard_name' => 'sanctum', 'tag' => 1]);
        Permission::create(['name' => "Registrar usuarios (misma regional)", 'guard_name' => 'sanctum', 'tag' => 1]);
        
        Permission::create(['name' => "Actualizar usuarios", 'guard_name' => 'sanctum', 'tag' => 1]);
        Permission::create(['name' => "Actualizar usuarios (misma regional)", 'guard_name' => 'sanctum', 'tag' => 1]);
        
        Permission::create(['name' => "Bloquear usuarios", 'guard_name' => 'sanctum', 'tag' => 1]);
        Permission::create(['name' => "Bloquear usuarios (misma regional)", 'guard_name' => 'sanctum', 'tag' => 1]);
        
        Permission::create(['name' => "Desbloquear usuarios", 'guard_name' => 'sanctum', "tag" => 1]);
        Permission::create(['name' => "Desbloquear usuarios (misma regional)", 'guard_name' => 'sanctum', "tag" => 1]);

        Permission::create(['name' => "Cambiar contraseñas", 'guard_name' => 'sanctum', "tag" => 1]);
        Permission::create(['name' => "Cambiar contraseñas (misma regional)", 'guard_name' => 'sanctum', "tag" => 1]);

        //rol permissions
        Permission::create(['name' => "Ver roles", 'guard_name' => 'sanctum', "tag" => 2]);
        Permission::create(['name' => "Registrar roles", 'guard_name' => 'sanctum', "tag" => 2]);
        Permission::create(['name' => "Actualizar roles", 'guard_name' => 'sanctum', "tag" => 2]);
        Permission::create(['name' => "Eliminar roles", 'guard_name' => 'sanctum', "tag" => 2]);

        //atencion externa permissions
        Permission::create(['name' => "Ver solicitudes de atención externa", 'guard_name' => 'sanctum', "tag" => 3]);
        Permission::create(['name' => "Ver solicitudes de atención externa (misma regional)", 'guard_name' => 'sanctum', "tag" => 3]);
        
        Permission::create(['name' => "Registrar solicitudes de atención externa", 'guard_name' => 'sanctum', "tag" => 3]);
        Permission::create(['name' => "Registrar solicitudes de atención externa (misma regional)", 'guard_name' => 'sanctum', "tag" => 3]);

        Permission::create(['name' => "Emitir solicitudes de atención externa", 'guard_name' => 'sanctum', "tag" => 3]);
        Permission::create(['name' => "Emitir solicitudes de atención externa (misma regional)", 'guard_name' => 'sanctum', "tag" => 3]);

        //lista mora
        Permission::create(["name" => "Ver la lista de mora", "guard_name" => "sanctum", "tag" => 4]);
        Permission::create(["name" => "Ver la lista de mora (misma regional)", "guard_name" => "sanctum", "tag" => 4]);
        Permission::create(["name" => "Agregar a la lista de mora", "guard_name" => "sanctum", "tag" => 4]);
        Permission::create(["name" => "Agregar a la lista de mora (misma regional)", "guard_name" => "sanctum", "tag" => 4]);
        Permission::create(["name" => "Quitar de la lista de mora", "guard_name" => "sanctum", "tag" => 4]);
        Permission::create(["name" => "Quitar de la lista de mora (misma regional)", "guard_name" => "sanctum", "tag" => 4]);

        //médicos
        Permission::create(["name" => "Ver médicos", "guard_name" => "sanctum", "tag" => 5]);
        Permission::create(["name" => "Ver médicos (misma regional)", "guard_name" => "sanctum", "tag" => 5]);
        Permission::create(["name" => "Registrar médicos", "guard_name" => "sanctum", "tag" => 5]);
        Permission::create(["name" => "Registrar médicos (misma regional)", "guard_name" => "sanctum", "tag" => 5]);
        Permission::create(["name" => "Actualizar médicos", "guard_name" => "sanctum", "tag" => 5]);
        Permission::create(["name" => "Actualizar médicos (misma regional)", "guard_name" => "sanctum", "tag" => 5]);
        Permission::create(["name" => "Actualizar el estado de los médicos", "guard_name" => "sanctum", "tag" => 5]);
        Permission::create(["name" => "Actualizar el estado de los médicos (misma regional)", "guard_name" => "sanctum", "tag" => 5]);

        //Proveedores
        Permission::create(["name" => "Ver proveedores", "guard_name" => "sanctum", "tag" => 6]);
        Permission::create(["name" => "Ver proveedores (misma regional)", "guard_name" => "sanctum", "tag" => 6]);
        Permission::create(["name" => "Registrar proveedores", "guard_name" => "sanctum", "tag" => 6]);
        Permission::create(["name" => "Registrar proveedores (misma regional)", "guard_name" => "sanctum", "tag" => 6]);
        Permission::create(["name" => "Actualizar proveedores", "guard_name" => "sanctum", "tag" => 6]);
        Permission::create(["name" => "Actualizar proveedores (misma regional)", "guard_name" => "sanctum", "tag" => 6]);
        Permission::create(["name" => "Actualizar el estado de los proveedores", "guard_name" => "sanctum", "tag" => 6]);
        Permission::create(["name" => "Actualizar el estado de los proveedores (misma regional)", "guard_name" => "sanctum", "tag" => 6]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::truncate();
    }
}
