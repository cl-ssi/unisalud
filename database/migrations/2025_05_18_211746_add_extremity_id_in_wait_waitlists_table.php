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
            $table->foreignId('extremity_id')->nullable()->constrained('wait_extremities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wait_waitlists', function (Blueprint $table) {
            $table->dropForeign(['extremity_id']);
            $table->dropColumn('extremity_id');
        });
    }
};
