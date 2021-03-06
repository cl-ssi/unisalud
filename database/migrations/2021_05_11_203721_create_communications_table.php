<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('communication_id')->nullable();
            $table->foreignId('cod_con_languaje_id')->nullable(); /** lenguaje */
            $table->foreignId('user_id')->nullable();
            $table->boolean('preferred')->nullable();

            $table->timestamps();

            $table->foreign('communication_id')->references('id')->on('communications');
            $table->foreign('cod_con_languaje_id')->references('id')->on('cod_con_languajes');
            $table->foreign('user_id')->references('id')->on('users');  

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('communication');
    }
}
