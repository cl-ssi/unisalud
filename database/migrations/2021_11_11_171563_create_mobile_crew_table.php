<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileCrewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('samu_mobile_crew', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('mobiles_in_service_id')->constrained('samu_mobiles_in_service');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('job_type_id')->constrained('samu_job_types');
            
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
        Schema::dropIfExists('samu_mobile_crew');
    }
}