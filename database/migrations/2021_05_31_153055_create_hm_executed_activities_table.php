<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHmExecutedActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Schema::create('mp_specialties', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->bigInteger('id_sigte');
        //     $table->bigInteger('id_n820');
        //     $table->string('specialty_name');
        //
        //     $table->timestamps();
        //     $table->softDeletes();
        // });

        Schema::create('mp_executed_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('correlative')->nullable();
            $table->datetime('programming_date')->nullable();
            $table->string('operating_room')->nullable();
            $table->integer('origin_request')->nullable();
            $table->string('origin_request_desc')->nullable();
            $table->string('profession')->nullable();
            $table->string('medic_rut')->nullable();
            $table->string('medic_dv')->nullable();
            $table->string('medic_name')->nullable();
            // $table->unsignedInteger('specialty_id');
            $table->string('medic_specialty')->nullable();
            $table->string('medic_specialty_desc')->nullable();
            $table->integer('intervention_procedure');
            $table->string('intervention_procedure_desc')->nullable();
            $table->integer('plane')->nullable();
            $table->string('plane_desc')->nullable();
            $table->integer('extremity')->nullable();
            $table->string('extremity_desc')->nullable();
            $table->integer('estimated_intervention_time');
            $table->datetime('tx_entrance_date')->nullable(); //FECHA_INGRESO_TX
            $table->integer('intervention_status')->nullable();
            $table->string('intervention_status_desc')->nullable();
            $table->datetime('intervention_start_date')->nullable(); //FECHA_INICIO_INTERVENCION
            $table->datetime('intervention_end_date')->nullable(); //FECHA_TERMINO_INTERVENCION
            $table->integer('surgery_category')->nullable();
            $table->string('surgery_category_desc')->nullable();
            $table->datetime('operating_room_entrance_date')->nullable(); //FECHA_INGRESO_PABELLON
            $table->datetime('surgery_room_entrance_date')->nullable(); //FECHA_INGRESO_QUIROFANO
            $table->datetime('surgery_room_exit_date')->nullable(); //FECHA_SALIDA_QUIROFANO
            $table->string('table_surgery_category')->nullable();
            $table->string('table_surgery_category_desc')->nullable();
            $table->integer('suspencion_cause')->nullable();
            $table->string('suspencion_cause_desc')->nullable();
            // $table->integer('year')->nullable();

            // $table->foreign('specialty_id')->references('id')->on('mp_specialties');

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
        Schema::dropIfExists('mp_executed_activities');
        // Schema::dropIfExists('mp_specialties');
    }
}
