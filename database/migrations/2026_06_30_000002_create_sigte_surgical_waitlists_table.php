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
        Schema::create('sigte_surgical_waitlists', function (Blueprint $table) {
            $table->id();

            $table->string('identifier')->nullable(); // ID_LOCAL

            $table->foreignId('user_id')->nullable()->constrained('users'); // Paciente
            $table->foreignId('requesting_professional_id')->nullable()->constrained('users'); // RUN_PROF_SOL
            $table->foreignId('resolving_professional_id')->nullable()->constrained('users');  // RUN_PROF_RESOL

            $table->string('health_service_id')->nullable(); // SERV_SALUD
            $table->foreignId('waitlist_entry_type_id')->nullable()->constrained('waitlist_entry_types'); // TIPO_PREST

            $table->string('complexity')->nullable(); // COMPLEJIDAD
            $table->foreignId('sigte_surgical_procedure_code_id')->nullable();
            $table->foreign('sigte_surgical_procedure_code_id', 'sigte_surgical_waitlists_procedure_code_foreign')
                ->references('id')->on('sigte_surgical_procedure_codes'); // PRESTA_MIN / PRESTA_EST

            $table->string('plano')->nullable();     // PLANO
            $table->string('extremity')->nullable(); // EXTREMIDAD

            $table->date('entry_date')->nullable(); // F_ENTRADA LE QX

            $table->foreignId('origin_establishment_id')->nullable()->constrained('organizations');  // ESTAB_ORIGEN
            $table->foreignId('destiny_establishment_id')->nullable()->constrained('organizations'); // ESTAB_DESTINO
            $table->string('referring_specialty')->nullable(); // E_OTOR_AT

            $table->string('suspected_diagnosis')->nullable(); // SOSPECHA_DIAG
            $table->string('confirmed_diagnosis')->nullable(); // CONFIR_DIAG

            $table->boolean('prais')->default(false); // PRAIS
            $table->foreignId('healthcare_type_id')->nullable()->constrained('healthcare_types'); // PREVISION

            $table->foreignId('region_id')->nullable()->constrained('regions');   // REGION
            $table->foreignId('commune_id')->nullable()->constrained('communes'); // COMUNA

            $table->string('sigte_id')->nullable(); // SIGTE_ID
            $table->string('status')->default('ingresado');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sigte_surgical_waitlists');
    }
};
