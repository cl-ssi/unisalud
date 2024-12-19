<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodConLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cod_con_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cod_con_location_id')->nullable();
            $table->foreignId('coding_id')->nullable();
            $table->foreignId('location_id')->nullable();

            $table->string('text');

            $table->foreign('cod_con_location_id')->references('id')->on('cod_con_locations');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('coding_id')->references('id')->on('codings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cod_con_location');
    }
}
