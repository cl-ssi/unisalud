<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Coded diagnosis (CIE-10) next to the free-text SOSPECHA_DIAG /
     * CONFIR_DIAG; it's what links a case to its GES health problems.
     */
    public function up(): void
    {
        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->foreignId('cie10_id')->nullable()->after('confirmed_diagnosis')->constrained('cie10');
        });
    }

    public function down(): void
    {
        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cie10_id');
        });
    }
};
