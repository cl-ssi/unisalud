<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpiTracingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('epi_tracings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id');

            //Datos Resultado
            $table->date('delivery_of_result')->nullable();
            $table->string('mechanism_of_result')->nullable();
            $table->string('observation_of_result')->nullable();

            //interconsulta
            $table->date('delivery_of_result')->nullable();
            


            $table->boolean('index')->nullable();

            $table->foreign('patient_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('epi_tracings');
    }
}
