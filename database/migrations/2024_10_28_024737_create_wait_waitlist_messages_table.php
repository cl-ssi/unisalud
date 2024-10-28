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
        Schema::create('wait_waitlist_messages', function (Blueprint $table) {
            $table->id();
            
            $table->string('discharge')->nullable(); // DEPENDE DE STATUS [EGRESO]
            $table->string('discharge')->nullable(); // DEPENDE DE STATUS [EGRESO]
            $table->text('text')->nullable();
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
        Schema::dropIfExists('wait_waitlist_messages');
    }
};
