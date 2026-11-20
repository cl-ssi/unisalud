<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TIPO_PREST of each Código MAI row, so the catalog can hold every type of
     * "Prestaciones_c-fechavigencia.xlsx" (hoja "prest total"). Only type 4
     * rows are valid PRESTA_MIN values for TIPO_PREST 4 waitlist rows.
     *
     * The coded rows loaded so far were all Tipo 4.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('demand_management_specialities', 'type')) {
            Schema::table('demand_management_specialities', function (Blueprint $table) {
                $table->string('type', 10)->nullable()->after('code');
                $table->index(['type', 'code']);
            });
        }

        DB::table('demand_management_specialities')
            ->whereNotNull('code')
            ->whereNull('type')
            ->update(['type' => '4']);
    }

    public function down(): void
    {
        Schema::table('demand_management_specialities', function (Blueprint $table) {
            $table->dropIndex(['type', 'code']);
            $table->dropColumn('type');
        });
    }
};
