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
            $table->text('suspected_diagnosis')->nullable()->change();
            $table->text('confirmed_diagnosis')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('odontology_waitlists', function (Blueprint $table) {
            $table->string('suspected_diagnosis')->nullable()->change();
            $table->string('confirmed_diagnosis')->nullable()->change();
        });
    }
};
