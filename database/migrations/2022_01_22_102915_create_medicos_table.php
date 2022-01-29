<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer("ci");
            $table->char("ci_complemento", 2)->default("");
            $table->string('apellido_paterno', 25)->nullable();
            $table->string('apellido_materno', 25)->nullable();
            $table->string('nombre', 50);
            $table->string('especialidad', 50);
            $table->tinyInteger("estado")->default(1);
            $table->foreignId("regional_id")->constrained("regionales");
            $table->timestamps();
            
            $table->fulltext(["apellido_paterno", "apellido_materno", "nombre"], "medicos_full_name");
            $table->fulltext(["especialidad"]);
            $table->unique(["ci", "ci_complemento", "regional_id"], "medicos_ci");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medicos');
    }
}
