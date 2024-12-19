<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attachment_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('contentType')->nullable(); /** Technically, a code is restricted to a string which has at least one character and no leading or trailing whitespace, and where there is no whitespace other than single spaces in the contents*/
            $table->foreignId('practitioner_id')->nullable();
            $table->string('languaje')->nullable();
        /** $table->base64Binary('data')->nullable(); */ 
            $table->string('url')->nullable();
            $table->unsignedInteger('size')->nullable();
        /** $table->base64Binary('hash')->nullable(); */ 
            $table->string('title')->nullable();
            $table->dateTime('creation')->nullable();/** Date attachment was first created */

            $table->timestamps();

            $table->foreign('attachment_id')->references('id')->on('attachments');
            $table->foreign('user_id')->references('id')->on('users');  
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
        Schema::dropIfExists('attachment');
    }
}
