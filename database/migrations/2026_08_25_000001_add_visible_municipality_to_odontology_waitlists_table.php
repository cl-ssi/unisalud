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
        Schema::table('odontology_waitlists', function (Blueprint $table) {
            $table->boolean('visible_municipality')->default(false)->after('pase_odontologico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('odontology_waitlists', function (Blueprint $table) {
            $table->dropColumn('visible_municipality');
        });
    }
};
