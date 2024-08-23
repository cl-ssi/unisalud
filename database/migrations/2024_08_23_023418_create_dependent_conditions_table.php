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
        Schema::create('dependent_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dependent_user_id')->nullable()->constrained('dependent_user');
            $table->foreignId('condition_id')->nullable()->constrained('condition');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependent_conditions');
    }
};
