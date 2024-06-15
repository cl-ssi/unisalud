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
        Schema::create('conditions', function (Blueprint $table) {
            $table->id();

            $table->string('identifier')->nullable();
            $table->enum('cod_con_clinical_status', [
                    'active',
                    'recurrence',
                    'relapse',
                    'inactive',
                    'remission',
                    'resolved',
                    'unknown'
                ])
                ->nullable();
            $table->enum('cod_con_verification_status', [
                    'unconfirmed',
                    'provisional',
                    'differential',
                    'confirmed',
                    'refuted',
                    'entered-in-error',
                ])
                ->nullable();

            $table->foreignId('cod_con_code_id')->nullable()->constrained('codings');
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conditions');
    }
};
