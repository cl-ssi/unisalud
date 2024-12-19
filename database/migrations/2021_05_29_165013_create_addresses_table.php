<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('period_id')->nullable();
            $table->enum('use',['home','work','temp','old','billing',
            ])->nullable();
            $table->enum('type',['postal','physical','both',
            ])->nullable();
            $table->string('text')->nullable();
            $table->string('line')->nullable();
            $table->string('apartment')->nullable();
            $table->string('suburb')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('country_id')->nullable();
            $table->foreignId('commune_id')->nullable();
            $table->string('postal_code')->nullable();
            $table->foreignId('region_id')->nullable();
            $table->boolean('actually')->default(false);
            $table->foreignId('organization_id')->nullable();
            $table->foreignId('practitioner_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('address_id')->references('id')->on('addresses');
            $table->foreign('period_id')->references('id')->on('periods');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('commune_id')->references('id')->on('communes');
            $table->foreign('region_id')->references('id')->on('regions');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('practitioner_id')->references('id')->on('practitioners');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
