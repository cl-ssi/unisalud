<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodConObservationCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cod_con_observation_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cod_con_obs_categories_id')->nullable();
            $table->foreignId('coding_id')->nullable();
            $table->string('text');            
            
            $table->foreign('cod_con_obs_categories_id')->references('id')->on('cod_con_observation_categories');
            $table->foreign('coding_id')->references('id')->on('codings');
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
        Schema::dropIfExists('cod_con_observation_categories');
    }
}
