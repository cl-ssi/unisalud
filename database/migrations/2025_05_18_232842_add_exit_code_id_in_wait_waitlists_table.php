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
            $table->foreignId('exit_code_id')
                ->after('destiny_organization_id')
                ->nullable()->constrained('wait_exit_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wait_waitlists', function (Blueprint $table) {
            $table->dropForeign(['exit_code_id']);
            $table->dropColumn('exit_code_id');
        });
    }
};
