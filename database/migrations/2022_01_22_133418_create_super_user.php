<?php

use App\Models\Role;
use App\Models\User;
use App\Models\ValueObjects\CarnetIdentidad;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSuperUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rol = Role::create([
            "name" => "super user", 
            "guard_name" => "sanctum"
        ]);

        $user = User::create([
            "ci" => new CarnetIdentidad(0, ""),
            "regional_id" => 1,
            "nombre" => "",
            "username" => "admin",
            "password" => 'password'
        ]);

        $user->syncRoles([$rol->name]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table("roles")->truncate();
        DB::table("users")->truncate();
    }
}
