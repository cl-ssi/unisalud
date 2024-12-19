<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable();
            $table->enum('status', ['proposed', 'pending', 'booked', 'arrived', 'fulfilled', 'cancelled', 'noshow', 'entered-in-error', 'checked-in', 'waitlist',
            ])->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('practitioner_id')->nullable();
            $table->foreignId('cod_con_cancel_reason_id')->nullable();
            $table->foreignId('cod_con_appointment_type_id')->nullable();
            $table->unsignedInteger('priority')->nullable();
            $table->string('description')->nullable();
            $table->dateTime('start')->nullable(); /** time that starts the appointment */
            $table->dateTime('end')->nullable();
            $table->dateTime('created')->nullable(); /** time that the appointment was created */
            $table->string('patient_instruction')->nullable();
            $table->foreignId('mp_theoretical_programming_id')->nullable();
            // tiene un periodo pero 0..*
            $table->foreignId('observation_id')->nullable();
            $table->timestamps();

            $table->foreign('appointment_id')->references('id')->on('appointments');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('practitioner_id')->references('id')->on('practitioners');
            $table->foreign('cod_con_cancel_reason_id')->references('id')->on('cod_con_cancelation_reasons');
            $table->foreign('cod_con_appointment_type_id')->references('id')->on('cod_con_appointment_types');
            $table->foreign('mp_theoretical_programming_id')->references('id')->on('mp_theoretical_programming');
            $table->foreign('observation_id')->references('id')->on('observations');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointment');
    }
}
