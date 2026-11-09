<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ventox_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('health_service_check')->nullable();
            $table->string('medical_center_control')->nullable();
            $table->string('equipment_type')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->enum('therapy_time', ['12', '15', '18', '24', 'night_shift'])->nullable();
            $table->enum('flow', ['0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9', '1', '2', '3', '4', '5'])->nullable();
            $table->json('risks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventox_user');
    }
};
