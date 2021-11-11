<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateSamuMobilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        

        Schema::create('samu_mobiles', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('plate')->nullable();
            $table->string('type')->nullable();
            $table->boolean('managed');
            $table->string('description')->nullable();
            $table->boolean('status')->default(true);
            //$table->foreignId('job_type_id')->after('user_id')->constrained('samu_job_types');

            /* Permite utilizar softdelete */
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
        Schema::dropIfExists('samu_mobiles');
    }

}