<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodConPractitionersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cod_con_practitioners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cod_con_practitioner_id')->nullable();
            $table->foreignId('coding_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('text');

            $table->foreign('cod_con_practitioner_id')->references('id')->on('cod_con_practitioners');
            $table->foreign('coding_id')->references('id')->on('codings');
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
        Schema::dropIfExists('cod_con_practitioner');
    }
}
