<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHumanNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('human_names', function (Blueprint $table) {
            $table->id();
            $table->foreignId('human_name_id')->nullable();
            $table->enum('use', ['official', 'temp', 'nickname', 'anonymous', 'old', 'maiden',])->nullable();
            $table->string('text')->nullable();
            $table->string('given')->nullable();
            $table->string('fathers_family')->nullable();
            $table->string('mothers_family')->nullable();
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->datetime('period_start')->default('1800-01-01 00:00:00');
            $table->string('period_end')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('practitioner_id')->nullable();

            $table->timestamps();
            $table->foreign('human_name_id')->references('id')->on('human_names');
            $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('practitioner_id')->references('id')->on('practitioners');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('human_names');
    }
}
