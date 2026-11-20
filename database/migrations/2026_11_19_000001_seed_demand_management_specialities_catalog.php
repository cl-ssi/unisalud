<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns for the Gestión Demanda specialty catalog: code = Código MAI
     * (PRESTA_MIN when TIPO_PREST = 4), text = Especialidad, name = Glosa.
     * The catalog rows themselves are loaded from "Prestaciones_c-fechavigencia.xlsx"
     * with the "Importar Excel" action on the Códigos Especialidad page
     * (App\Imports\DemandManagementSpecialityCatalogImport).
     *
     * Also adds exit_specialty_id for PRESTA_MIN_SALIDA of TIPO_PREST 4 rows.
     *
     * Each step is guarded so the migration can resume after a partial run
     * (MySQL DDL is not transactional).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('demand_management_specialities', 'name')) {
            Schema::table('demand_management_specialities', function (Blueprint $table) {
                $table->text('name')->nullable()->after('text');
            });
        }

        if (! Schema::hasIndex('demand_management_specialities', ['code'])) {
            Schema::table('demand_management_specialities', function (Blueprint $table) {
                $table->index('code');
            });
        }

        if (! Schema::hasColumn('demand_management_waitlists', 'exit_specialty_id')) {
            Schema::table('demand_management_waitlists', function (Blueprint $table) {
                $table->foreignId('exit_specialty_id')->nullable()->after('specialty_id')->constrained('demand_management_specialities');
            });
        }
    }

    public function down(): void
    {
        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exit_specialty_id');
        });

        Schema::table('demand_management_specialities', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropColumn('name');
        });
    }
};
