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
        Schema::create('wait_events', function (Blueprint $table) {
            $table->id();

            $table->string('status')->nullable(); // [NO DERIVADO - DERIVADO - CITADO - ATENDIDO - INASISTENTE - INCONTACTABLE - EGRESADO]
            $table->dateTime('registered_at')->nullable();
            $table->text('text')->nullable();
            $table->string('discharge')->nullable(); // DEPENDE DE STATUS [EGRESO]
            $table->dateTime('appointment_at')->nullable();  // DEPENDE STATUS [CITADO - ATENDIDO]
            $table->foreignId('waitlist_id')->nullable()->constrained('wait_waitlists');
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
        Schema::dropIfExists('wait_events');
    }
};
