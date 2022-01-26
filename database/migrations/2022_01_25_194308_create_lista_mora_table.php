<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListaMoraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lista_mora', function (Blueprint $table) {
            $table->id();
            $table->char("empleador_id", 15);
            $table->string("numero_patronal", 10);
            $table->string("nombre", 255);
            $table->foreignId("regional_id")->constrained("regionales");
            $table->timestamps();

            $table->unique(["empleador_id", "regional_id"]);
            $table->fulltext(["nombre"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lista_mora');
    }
}
