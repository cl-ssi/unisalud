<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable();
            $table->enum('status', ['active', 'suspended', 'inactive',
            ])->nullable(); /**code() Technically, a code is restricted to a string which has at least one character and no leading or trailing whitespace, and where there is no whitespace other than single spaces in the contents*/
            $table->foreignId('coding_id')->nullable();
            $table->string('name')->nullable();
            $table->string('alias')->nullable(); /** 0..* */
            $table->string('description')->nullable();
            $table->enum('mode', ['instance', 'kind',
            ])->nullable();
            $table->foreignId('address_id')->nullable();
            $table->foreignId('cod_con_physical_type_id')->nullable(); /**site,building,wing,ward,leve,corridor,room,bed,vehicle,house,cabinet,road,area,jurisdiction */
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('altitude')->nullable();

            // $table->foreignId('cod_con_organization_id')->nullable(); /** Refers to the organization responsible for provisioning and upkeep */
            
            $table->foreignId('organization_id')->nullable();
            

            $table->string('AvailabilityExceptions')->nullable();

            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('coding_id')->references('id')->on('codings');
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->foreign('cod_con_physical_type_id')->references('id')->on('cod_con_physical_types');
            // $table->foreign('cod_con_organization_id')->references('id')->on('cod_con_organizations');
            $table->foreign('organization_id')->references('id')->on('organizations');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location');
    }
}
