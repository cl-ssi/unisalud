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
        Schema::table('wait_waitlists', function (Blueprint $table) {
            $table->foreignId('prevision_id')
                ->after('destiny_organization_id')
                ->nullable()->constrained('wait_previsiones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wait_waitlists', function (Blueprint $table) {
            $table->dropForeign(['prevision_id']);
            $table->dropColumn('prevision_id');
        });
    }
};
