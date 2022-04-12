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
            //$table->foreignId('type_id')->constrained('samu_mobile_types');
            $table->string('description')->nullable();
            $table->boolean('managed')->default(false);
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('samu_mobiles');
    }

}