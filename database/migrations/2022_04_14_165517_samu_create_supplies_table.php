<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SamuCreateSuppliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('samu_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->string('code');
            $table->string('name');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->integer('value');
            $table->softDeletes();
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
        Schema::dropIfExists('samu_supplies');
    }
}
