<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHmRrhhTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('mp_rrhh', function (Blueprint $table) {
        //     $table->integer('id_deis')->nullable();
        //     $table->integer('cod_estab_sirh')->nullable();
        //     $table->integer('rut')->unsigned()->unique();
        //     $table->char('dv',1);
        //     $table->boolean('risk_group')->default(0);
        //     $table->boolean('missing_condition')->default(0);
        //     $table->string('missing_reason')->nullable();
        //     $table->string('name');
        //     $table->string('fathers_family');
        //     $table->string('mothers_family')->nullable();
        //     $table->string('job_title');
        //     //$table->unsignedBigInteger('user_id');
        //
        //     //$table->foreign('user_id')->references('id')->on('users');
        //     $table->timestamps();
        //     $table->softDeletes();
        // });

        Schema::create('mp_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('service_name')->nullable();
            $table->string('color')->nullable();
  
            $table->timestamps();
            $table->softDeletes();
        });
  
        Schema::create('mp_user_services', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('principal')->default(0);
  
            $table->foreign('service_id')->references('id')->on('mp_services');
            $table->foreign('user_id')->references('id')->on('users');
  
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('service_code')->nullable();
            // $table->unsignedInteger('rut'); //fk
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('service_id')->default(1);            
            $table->string('law')->nullable();
            $table->integer('contract_id')->nullable();
            $table->integer('weekly_hours')->nullable();
            $table->string('shift_system')->nullable();
            $table->string('obs')->nullable();
            $table->integer('legal_holidays')->nullable();
            $table->integer('compensatory_rest')->nullable();
            $table->integer('administrative_permit')->nullable();
            $table->integer('training_days')->nullable();
            $table->integer('breastfeeding_time')->nullable();
            $table->integer('weekly_collation')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('unit')->nullable();
            $table->string('unit_code')->nullable();
            $table->integer('year')->nullable();

            // $table->foreign('rut')->references('rut')->on('mp_rrhh')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('service_id')->references('id')->on('mp_services');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_specialties', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('id_specialty')->nullable();
            $table->string('id_sigte')->nullable();
            // $table->bigInteger('id_sigte')->nullable();
            $table->string('specialty_name');
            $table->string('color')->nullable();
            //$table->unsignedBigInteger('user_id');

            //$table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_user_specialties', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('specialty_id');            
            $table->boolean('principal')->default(0);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('specialty_id')->references('id')->on('mp_specialties');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_professions', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('id_profession')->nullable();
            // $table->bigInteger('id_sigte')->nullable();
            $table->string('profession_name');
            $table->string('color')->nullable();
            //$table->unsignedBigInteger('user_id');

            //$table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_user_professions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('profession_id');
            $table->boolean('principal')->default(0);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('profession_id')->references('id')->on('mp_professions')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_mother_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            //$table->unsignedBigInteger('user_id');

            //$table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_activity_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            //$table->unsignedBigInteger('user_id');

            //$table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_activities', function (Blueprint $table) {
            // $table->integer('id')->unsigned()->unique();
            $table->increments('id');
            $table->bigInteger('id_activity')->nullable();
            $table->unsignedInteger('mother_activity_id')->nullable();
            $table->unsignedInteger('activity_type_id')->nullable();
            $table->string('activity_name');
            $table->LONGTEXT('description')->nullable();
            $table->boolean('performance')->default(0);
            $table->boolean('programmable')->default(0);
            //$table->unsignedBigInteger('user_id');

            //$table->foreign('user_id')->references('id')->on('users');
            $table->foreign('mother_activity_id')->references('id')->on('mp_mother_activities')->onDelete('cascade');
            $table->foreign('activity_type_id')->references('id')->on('mp_activity_types')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_profession_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('profession_id');
            $table->unsignedInteger('activity_id');
            $table->decimal('performance', 8, 2)->nullable();

            $table->foreign('profession_id')->references('id')->on('mp_professions');
            $table->foreign('activity_id')->references('id')->on('mp_activities');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_specialty_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('specialty_id');
            $table->unsignedInteger('activity_id');
            $table->decimal('performance', 8, 2)->nullable();

            $table->foreign('specialty_id')->references('id')->on('mp_specialties');
            $table->foreign('activity_id')->references('id')->on('mp_activities');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mp_unscheduled_programming', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('contract_id')->nullable();
            // $table->unsignedInteger('rut')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('specialty_id')->nullable();
            $table->unsignedInteger('profession_id')->nullable();
            $table->unsignedInteger('activity_id')->nullable();

            $table->decimal('assigned_hour', 8, 2)->nullable();
            $table->decimal('hour_performance', 8, 2)->nullable();
            $table->string('year')->nullable();

            $table->foreign('contract_id')->references('id')->on('mp_contracts')->onDelete('cascade');
            // $table->foreign('rut')->references('rut')->on('mp_rrhh')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('specialty_id')->references('id')->on('mp_specialties')->onDelete('cascade');
            $table->foreign('profession_id')->references('id')->on('mp_professions')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('mp_activities')->onDelete('cascade');

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
        Schema::dropIfExists('mp_user_professions');
        Schema::dropIfExists('mp_user_specialties');
        Schema::dropIfExists('mp_unscheduled_programming');
        Schema::dropIfExists('mp_profession_activities');
        Schema::dropIfExists('mp_specialty_activities');
        Schema::dropIfExists('mp_activities');
        Schema::dropIfExists('mp_activity_types');
        Schema::dropIfExists('mp_mother_activities');
        Schema::dropIfExists('mp_professions');
        Schema::dropIfExists('mp_specialties');
        Schema::dropIfExists('mp_contracts');
        Schema::dropIfExists('mp_rrhh');
        Schema::dropIfExists('mp_services');
        Schema::dropIfExists('mp_user_services');
    }
}
