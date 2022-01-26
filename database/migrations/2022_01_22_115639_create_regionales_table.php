<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRegionalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regionales', function (Blueprint $table) {
            $table->id();
            $table->string("nombre", 25)->unique();
            $table->point("ubicacion")->nullable();
            $table->timestamps();
        });

        DB::table("regionales")->insert([
            ["nombre" => "La Paz", "ubicacion" => DB::raw("point(-68.12084964, -16.48924376)")],
            ["nombre" => "Cochabamba", "ubicacion" => DB::raw("point(-66.16439053, -17.41289645)")],
            ["nombre" => "Santa Cruz", "ubicacion" => DB::raw("point(-63.18089257, -17.77235572)")],
            ["nombre" => "Oruro", "ubicacion" => DB::raw("point(-67.09389215, -17.97162166)")],
            ["nombre" => "Potosí", "ubicacion" => DB::raw("point(-65.75612412, -19.57299744)")],
            ["nombre" => "Sucre", "ubicacion" => DB::raw("point(-65.25897680, -19.03521513)")],
            ["nombre" => "Tarija", "ubicacion" => DB::raw("point(-64.73555852, -21.46965905)")],
            ["nombre" => "Trinidad", "ubicacion" => DB::raw("point(-64.90199697, -14.83191343)")],
            ["nombre" => "Cobija", "ubicacion" => DB::raw("point(-68.78055630, -11.03455697)")],
            ["nombre" => "Tupiza", "ubicacion" => DB::raw("point(-65.71942090, -21.43824256)")],
            ["nombre" => "Riberalta", "ubicacion" => DB::raw("point(-66.05662820, -11.00628171)")]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regionales');
    }
}
