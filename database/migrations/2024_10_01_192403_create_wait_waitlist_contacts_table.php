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
        Schema::create('wait_contacts', function (Blueprint $table) {
            $table->id();

            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('contacted_at')->nullable();
            $table->text('text')->nullable();
            $table->foreignId('waitlist_id')->nullable()->constrained('wait_waitlists');
            $table->foreignId('register_user_id')->nullable()->constrained('users');
            $table->foreignId('organization_user_id')->nullable()->constrained('organizations');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wait_contacts');
    }
};
