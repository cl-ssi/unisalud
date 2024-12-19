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
        Schema::create('dependent_user', function (Blueprint $table) {
            $table->id();

            $table->string('identifier')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->text('diagnosis')->nullable();

            $table->date('check_in_date')->nullable();

            $table->date('check_out_date')->nullable();

            $table->integer('integral_visits')->nullable();

            $table->date('last_integral_visit')->nullable();

            $table->integer('treatment_visits')->nullable();

            $table->date('last_treatment_visit')->nullable();

            $table->enum('barthel', [
                'independent',
                'slight',
                'moderate',
                'severe',
                'total',
            ])->nullable();

            $table->boolean('empam')->nullable();

            $table->boolean('eleam')->nullable();

            $table->boolean('upp')->nullable();

            $table->boolean('elaborated_plan')->nullable();

            $table->boolean('evaluated_plan')->nullable();

            $table->date('pneumonia')->nullable();

            $table->date('influenza')->nullable();

            $table->date('covid_19')->nullable();

            $table->text('extra_info')->nullable();

            $table->boolean('tech_aid')->nullable();

            $table->date('tech_aid_date')->nullable();

            $table->boolean('nutrition_assistance')->nullable();

            $table->date('nutrition_assistance_date')->nullable();

            $table->boolean('flood_zone')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });

        Schema::create('condition_dependent_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\DependentUser::class)->constrained('dependent_user')->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Condition::class)->constrained('condition')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condition_dependent_user');
        Schema::dropIfExists('dependent_user');
    }
};
