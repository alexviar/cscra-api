<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32);
            $table->string('password', 255);
            $table->integer("ci");
            $table->char("ci_complemento", 2)->default("");
            $table->string('apellido_paterno', 25)->nullable();
            $table->string('apellido_materno', 25)->nullable();
            $table->string('nombre', 50);
            $table->tinyInteger("estado")->default(1);
            $table->foreignId("regional_id")->constrained("regionales");
            $table->rememberToken();
            $table->timestamps();
            
            $table->fulltext(["apellido_paterno", "apellido_materno", "nombre"], "full_name");
            $table->unique(["ci", "ci_complemento", "regional_id"], "ci");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
