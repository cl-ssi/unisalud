<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conditions', function (Blueprint $table) {
            $table->id();

            $table->string('identifier')->nullable();

            $table->enum('cod_con_clinical_status', [
                    'active',
                    'recurrence',
                    'relapse',
                    'inactive',
                    'remission',
                    'resolved',
                    'unknown'
                ])
                ->nullable();
            $table->enum('cod_con_verification_status', [
                    'unconfirmed',
                    'provisional',
                    'differential',
                    'confirmed',
                    'refuted',
                    'entered-in-error',
                ])
                ->nullable();


            $table->foreignId('cod_con_code_id')->nullable()->constrained('codings');

            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->string('user_condition')->nullable();

            $table->text('diagnosis')->nullable();

            $table->date('check_in_date')->nullable();

            $table->date('check_out_date')->nullable();

            $table->integer('integral_visits')->nullable();

            $table->integer('treatment_visits')->nullable();

            $table->date('last_integral_visit')->nullable();

            $table->date('last_treatment_visit')->nullable();

            $table->string('barthel')->nullable();

            $table->string('empam')->nullable();

            $table->string('eleam')->nullable();

            $table->string('upp')->nullable();

            $table->string('elaborated_plan')->nullable();

            $table->string('evaluated_plan')->nullable();

            $table->string('pneumonia')->nullable();

            $table->string('influenza')->nullable();

            $table->string('covid_19')->nullable();

            $table->text('extra_info')->nullable();

            $table->string('tech_aid')->nullable();

            $table->string('nutrition_assistance')->nullable();

            $table->string('flood_zone')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conditions');
    }
};
