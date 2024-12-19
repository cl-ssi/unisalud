<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('codings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coding_id')->nullable();
            $table->string('system')->nullable();
            $table->string('version')->nullable();
            $table->string('code')->nullable(); /**code() Technically, a code is restricted to a string which has at least one character and no leading or trailing whitespace, and where there is no whitespace other than single spaces in the contents*/            
            $table->string('display')->nullable();
            $table->boolean('userSelected')->nullable();

            $table->timestamps();
            $table->foreign('coding_id')->references('id')->on('codings');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coding');
    }
}
