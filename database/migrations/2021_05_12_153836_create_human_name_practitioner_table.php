<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHumanNamePractitionerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('human_name_practitioner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('human_name_practitioner_id')->nullable();
            $table->enum('use', ['usual', 'official', 'temp', 'nickname', 'anonymous', 'old', 'maiden',
            ])->nullable();
            $table->string('text')->nullable();
            $table->string('family')->nullable();
            $table->string('given')->nullable();
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();

            $table->timestamps();
            $table->foreign('human_name_practitioner_id')->references('id')->on('human_name_practitioner');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('human_name_practitioner');
    }
}