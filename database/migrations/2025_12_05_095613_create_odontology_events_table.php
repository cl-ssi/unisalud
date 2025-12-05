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
        Schema::create('odontology_events', function (Blueprint $table) {
            $table->id();

            $table->string('status')->nullable();
            $table->dateTime('registered_at')->nullable();
            $table->text('text')->nullable();
            $table->string('discharge')->nullable();
            $table->dateTime('appointment_at')->nullable();
            $table->foreignId('waitlist_id')->nullable()->constrained('odontology_waitlists');
            $table->foreignId('register_user_id')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontology_events');
    }
};
