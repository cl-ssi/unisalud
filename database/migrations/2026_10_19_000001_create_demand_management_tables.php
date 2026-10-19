<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestión Demanda: same structure as the odontology waitlist tables,
     * without pase_odontologico.
     */
    public function up(): void
    {
        foreach (['demand_management_specialities', 'demand_management_health_care_services', 'demand_management_medical_benefits'] as $catalog) {
            Schema::create($catalog, function (Blueprint $table) {
                $table->id();

                $table->string('code')->nullable();
                $table->string('text')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::create('demand_management_waitlists', function (Blueprint $table) {
            $table->id();
            $table->string('health_service_id')->nullable(); // SERV_SALUD
            $table->string('identifier')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('minsal_specialty_id')->nullable()->constrained('minsal_specialties');   // PRESTA_MIN
            $table->string('plano')->nullable();    // PLANO
            $table->string('extremity')->nullable();    // EXTREMIDAD
            $table->foreignId('wait_health_care_service_id')->nullable()->constrained('demand_management_health_care_services'); // PRESTA_EST
            $table->date('entry_date')->nullable();     // F_ENTRADA
            $table->foreignId('origin_establishment_id')->nullable()->constrained('organizations'); // ESTAB_ORIG
            $table->foreignId('destiny_establishment_id')->nullable()->constrained('organizations');    // ESTAB_DEST
            $table->date('exit_date')->nullable();     // F_SALIDA
            $table->string('exit_code')->nullable();     // C_SALIDA
            $table->string('referring_specialty')->nullable();    // E_OTOR_AT
            $table->foreignId('exit_minsal_specialty_id')->nullable()->constrained('minsal_specialties');   // PRESTA_MIN_SALIDA
            $table->foreignId('waitlist_entry_type_id')->nullable()->constrained('waitlist_entry_types');   // TIPO_PREST
            $table->string('prais')->nullable(); // PRAIS
            $table->boolean('visible_municipality')->default(false);
            $table->foreignId('region_id')->nullable()->constrained('regions'); // REGION
            $table->foreignId('commune_id')->nullable()->constrained('communes'); // COMUNA
            $table->text('suspected_diagnosis')->nullable();   // SOSPECHA_DIAG
            $table->text('confirmed_diagnosis')->nullable();   // CONFIR_DIAG
            $table->date('appointment_date')->nullable();   // F_CITACION
            $table->foreignId('requesting_professional_id')->nullable()->constrained('users');  // RUN_PROF_SOL
            $table->foreignId('resolving_professional_id')->nullable()->constrained('users');   // RUN_PROF_RESOL
            $table->string('sigte_id')->nullable(); // SIGTE_ID
            $table->string('local_id')->nullable(); // ID_LOCAL
            $table->foreignId('healthcare_type_id')->nullable()->constrained('healthcare_types'); // PREVICION
            $table->foreignId('specialty_id')->nullable()->constrained('demand_management_specialities');   // ESPECIALIDAD
            $table->foreignId('establishment_id')->nullable()->constrained('organizations'); // ESTABLECIMIENTO
            $table->string('pediatric')->nullable(); // PEDIATRICO
            $table->string('lb')->nullable(); // LB
            $table->string('status')->nullable();
            $table->string('result')->nullable();   // RESULTADO
            $table->float('waitlistAge')->nullable();   // EDAD
            $table->integer('waitlistYear')->nullable();   // AÑO
            $table->string('worker')->nullable();   // Funcionario
            $table->string('iqType')->nullable();   // Típo de IQ
            $table->string('oncologic')->nullable();   // Oncologico
            $table->foreignId('origin_commune_id')->nullable()->constrained('communes'); // Comuna de Origen
            $table->string('fonasa')->nullable();   // fonasa
            $table->string('praisUser')->nullable();   // Usuario PRAIS
            $table->string('lbPrais')->nullable();   // LB PRAIS
            $table->string('lbUrinary')->nullable();   // LB INCONTINENCIA URINARIA
            $table->string('exitError')->nullable();   // Error Egreso
            $table->string('lbIqOdonto')->nullable();   // LB IQ ODONTO
            $table->string('procedureType')->nullable();   // Tipo Procedimiento
            $table->string('sename')->nullable();   // SENAME
            $table->foreignId('wait_medical_benefit_id')->nullable()->constrained('demand_management_medical_benefits'); // TIPO_PRESTACION
            $table->integer('elapsed_days')->nullable();   // DIAS_PASADOS
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('demand_management_events', function (Blueprint $table) {
            $table->id();

            $table->string('status')->nullable();
            $table->dateTime('registered_at')->nullable();
            $table->text('text')->nullable();
            $table->string('discharge')->nullable();
            $table->dateTime('appointment_at')->nullable();
            $table->foreignId('waitlist_id')->nullable()->constrained('demand_management_waitlists');
            $table->foreignId('register_user_id')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('demand_management_contacts', function (Blueprint $table) {
            $table->id();

            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('contacted_at')->nullable();
            $table->text('text')->nullable();
            $table->foreignId('waitlist_id')->nullable()->constrained('demand_management_waitlists');
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
        Schema::dropIfExists('demand_management_contacts');
        Schema::dropIfExists('demand_management_events');
        Schema::dropIfExists('demand_management_waitlists');
        Schema::dropIfExists('demand_management_medical_benefits');
        Schema::dropIfExists('demand_management_health_care_services');
        Schema::dropIfExists('demand_management_specialities');
    }
};
