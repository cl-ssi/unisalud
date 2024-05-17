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
        Schema::table('users', function (Blueprint $table) {
            $table->string('sex')->nullable()->change();
            $table->string('gender')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('sex', ['female', 'male', 'other', 'unknown'])->nullable()->change();
            $table->enum('gender', ['female', 'male', 'non-binary', 'transgender-male', 'transgender-female', 'other', 'non-disclose'])->nullable()->change();
        });
    }
};
