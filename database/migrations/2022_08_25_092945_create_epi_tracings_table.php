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
            $table->foreignId('suspect_case_id');
            $table->foreignId('patient_id');

            //3 Entrega de Resultado
            $table->date('delivery_of_result_1')->nullable();
            $table->string('mechanism_of_result_1')->nullable();
            $table->string('observation_of_result_1')->nullable();
            $table->date('delivery_of_result_2')->nullable();
            $table->string('mechanism_of_result_2')->nullable();
            $table->string('observation_of_result_2')->nullable();
            $table->date('delivery_of_result_3')->nullable();
            $table->string('mechanism_of_result_3')->nullable();
            $table->string('observation_of_result_3')->nullable();

            //Interconsulta
            $table->date('date_of_sic')->nullable();
            $table->string('polyclinic_sic')->nullable();

            //Notificación
            $table->date('date_of_notification')->nullable();
            $table->unsignedInteger('epi_notification')->nullable();
            $table->string('cie10name_notification')->nullable();

            //seguimiento
            $table->boolean('index')->nullable();
            $table->datetime('next_control_at')->nullable();
            $table->unsignedSmallInteger('status')->nullable();
            $table->foreignId('establishment_id');
            $table->date('date_of_last_birth')->nullable();
            $table->string('occupation')->nullable();
            $table->string('responsible_family_member')->nullable();
            $table->text('allergies')->nullable();
            $table->text('common_use_drugs')->nullable();
            $table->text('morbid_history')->nullable();
            $table->text('family_history')->nullable();
            $table->text('indications')->nullable();
            $table->text('observations')->nullable();


           
            $table->foreign('patient_id')->references('id')->on('users');
            $table->foreign('suspect_case_id')->references('id')->on('epi_suspect_cases');
            $table->foreign('establishment_id')->references('id')->on('organizations');
            
            $table->timestamps();
            $table->softDeletes();
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
