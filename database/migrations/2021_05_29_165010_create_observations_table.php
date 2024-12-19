<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->nullable();
            $table->enum('status', ['registered', 'preliminar', 'final', 'amended', 'corrected', 'cancelled', 'entered-in-error', 'unknown',
            ])->nullable();
            //cod con categorie
            $table->foreignId('cod_con_obs_categories_id')->nullable();
            $table->enum('type', ['doctor', 'patient'])->nullable();
            $table->string('description')->nullable();

            $table->timestamps();
            $table->foreign('observation_id')->references('id')->on('observations');
            $table->foreign('cod_con_obs_categories_id')->references('id')->on('cod_con_observation_categories');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('observations');
    }
}
