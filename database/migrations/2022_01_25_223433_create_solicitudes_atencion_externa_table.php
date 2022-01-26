<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitudesAtencionExternaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitudes_atencion_externa', function (Blueprint $table) {
            $table->id();
            $table->date("fecha");
            $table->char("paciente_id", 15); 
            $table->char("titular_id", 15)->nullable(); 
            $table->char("empleador_id", 15);
            $table->string("prestacion", 100);
            $table->foreignId("medico_id")->constrained("medicos");
            $table->foreignId("proveedor_id")->constrained("proveedores");
            $table->foreignId("regional_id")->constrained("regionales");
            $table->foreignId("login")->constrained("users");
            $table->timestamps();

            $table->fulltext(["prestacion"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudes_atencion_externa');
    }
}
