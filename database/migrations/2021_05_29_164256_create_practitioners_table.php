<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePractitionersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('practitioners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practitioner_id')->nullable();
            $table->boolean('active')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('organization_id')->nullable();
            $table->unsignedInteger('specialty_id')->nullable();
            $table->unsignedInteger('profession_id')->nullable();
//            $table->foreignId('specialty_id')->nullable();
            $table->foreign('practitioner_id')->references('id')->on('practitioners');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('specialty_id')->references('id')->on('mp_specialties');
            $table->foreign('profession_id')->references('id')->on('mp_professions');
            $table->string('job_title')->nullable();
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
        Schema::dropIfExists('practitioner');
    }
}
