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
        Schema::create('wait_waitlists', function (Blueprint $table) {
            $table->id();

            $table->string('identifier')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('plano')->nullable();
            $table->string('extremity')->nullable();
            $table->foreignId('wait_health_care_service_id')->nullable()->constrained('wait_health_care_services');
            $table->foreignId('cie10_id')->nullable()->constrained('cie10');
            $table->string('sigte_id')->nullable();
            $table->foreignId('wait_medical_benefit_id')->nullable()->constrained('wait_medical_benefits');
            $table->foreignId('wait_specialty_id')->nullable()->constrained('wait_specialties');
            $table->foreignId('organization_id')->nullable()->constrained('organizations');
            $table->foreignId('commune_id')->nullable()->constrained('communes');
            $table->string('status')->nullable();
            $table->foreignId('destiny_organization_id')->nullable()->constrained('organizations');
            $table->dateTime('attention_date')->nullable();  // FECHA_ATENCION 
            $table->string('attended')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wait_waitlists');
    }
};
