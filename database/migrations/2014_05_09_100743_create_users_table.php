<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->boolean('active');
            $table->enum('sex', ['female', 'male', 'other', 'unknown'])->nullable();
            $table->enum('gender', ['female', 'male', 'non-binary', 'transgender-male', 'transgender-female', 'other', 'non-disclose'])->nullable();
            $table->date('birthday')->nullable();
            $table->datetime('deceased_datetime')->nullable();
            $table->foreignId('cod_con_marital_id')->nullable(); /** marriage  */
            $table->integer('multiple_birth')->nullable();
            $table->foreignId('nationality_id')->nullable();
            $table->string('team')->nullable();

           /** Este campo viene por defecto en laravel */
            $table->string('password')->nullable();

            /* necesario para indicar que el login fue a través de CU */
            $table->boolean('claveunica')->nullable();

            /* Lugar donde almacenar el ID Fhir al crear un recurso tipo Patient */
            $table->string('fhir_id')->nullable();

            /* Propios de laravel */
            $table->rememberToken();
            $table->timestamps();

            /* Permite utilizar softdelete */
            $table->softDeletes();

            /**  FOREIGN KEYS  */
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('cod_con_marital_id')->references('id')->on('cod_con_maritals');
            $table->foreign('nationality_id')->references('id')->on('countries');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
