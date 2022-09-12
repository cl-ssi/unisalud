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

            //Entrega de Resultado
            $table->date('delivery_of_result')->nullable();
            $table->string('mechanism_of_result')->nullable();
            $table->string('observation_of_result')->nullable();

            //Interconsulta
            $table->date('date_of_sic')->nullable();
            $table->string('polyclinic_sic')->nullable();

            //Notificación
            $table->date('date_of_notification')->nullable();
            $table->unsignedInteger('epi_notification')->nullable();
            $table->string('cie10name_notification')->nullable();

            


            

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
