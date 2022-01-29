<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger("tipo");
            $table->string("nit", 20);
            $table->integer("ci")->nullable();
            $table->char("ci_complemento", 2)->nullable();
            $table->string('apellido_paterno', 25)->nullable();
            $table->string('apellido_materno', 25)->nullable();
            $table->string('nombre', 100);
            $table->string('especialidad', 50)->nullable();
            $table->tinyInteger("estado")->default(1);

            $table->point("ubicacion");
            $table->string("direccion", 80);
            $table->integer("telefono1");
            $table->integer("telefono2")->nullable();

            $table->foreignId("regional_id")->constrained("regionales");
            $table->timestamps();
            
            $table->fulltext(["apellido_paterno", "apellido_materno", "nombre"], "full_name");
            $table->fulltext(["especialidad"]);
            $table->unique(["ci", "ci_complemento", "regional_id"], "ci");
            $table->unique(["nit", "regional_id"], "nit");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proveedores');
    }
}
