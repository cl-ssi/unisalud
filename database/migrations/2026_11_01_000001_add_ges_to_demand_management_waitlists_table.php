<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether the case is covered by GES (the "GES" column of the import file).
     */
    public function up(): void
    {
        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->boolean('ges')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('demand_management_waitlists', function (Blueprint $table) {
            $table->dropColumn('ges');
        });
    }
};
