<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinorAuthorizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aps_authorizations_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('status')->default(1);
            
            $table->timestamps();
            $table->softDeletes();
        });



        Schema::create('aps_minor_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('aps_authorizations_types');
            $table->integer('run');
            $table->string('dv');
            $table->string('names');
            $table->string('fathers_family');
            $table->string('mothers_family');

            $table->date('authorization_date');
            $table->boolean('authorized')->default(null);

            $table->foreignId('authorizer_id')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aps_minor_authorizations');
        Schema::dropIfExists('aps_authorizations_types');
    }
}
