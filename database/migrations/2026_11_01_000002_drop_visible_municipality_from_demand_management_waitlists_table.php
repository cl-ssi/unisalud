<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestión Demanda no longer limits municipality users to flagged cases.
     */
    public function up(): void
    {
        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->dropColumn('visible_municipality');
        });
    }

    public function down(): void
    {
        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->boolean('visible_municipality')->default(false);
        });
    }
};
