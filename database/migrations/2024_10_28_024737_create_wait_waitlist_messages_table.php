<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wait_messages', function (Blueprint $table) {
            $table->id();
            
            $table->string('priority')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('waitlist_id')->nullable()->constrained('wait_waitlists');
            $table->foreignId('from_user_id')->nullable()->constrained('users');
            $table->foreignId('to_user_id')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wait_messages');
    }
};
