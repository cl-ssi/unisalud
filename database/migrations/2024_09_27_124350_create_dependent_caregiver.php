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
        Schema::create('dependent_caregiver', function (Blueprint $table) {
            
            $table->id();
            
            $table->foreignId('dependent_user_id')->nullable()->constrained('dependent_user');
            
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->string('relative')->nullable();

            $table->enum('healthcare_type', [
                'FONASA A',
                'FONASA B',
                'FONASA C',
                'FONASA D',
                'ISAPRE',
                'PRAIS',
            ])->nullable();

            $table->boolean('empam')->nullable();

            $table->boolean('zarit')->nullable();

            $table->text('immunizations')->nullable();

            $table->boolean('elaborated_plan')->nullable();

            $table->boolean('evaluated_plan')->nullable();

            $table->boolean('trained')->nullable();

            $table->boolean('stipend')->nullable();
            
            $table->softDeletes();
            
            $table->timestamps();
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependent_caregiver');
    }
};
