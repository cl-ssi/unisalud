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
        Schema::table('cie10', function (Blueprint $table) {
            Schema::table('cie10', function (Blueprint $table) {
                // Cambiamos el tipo de la columna 'id' a BIGINT sin signo
                $table->unsignedBigInteger('id')->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cie10', function (Blueprint $table) {
            // Volvemos a cambiar la columna 'id' a INT sin signo
            $table->unsignedInteger('id')->change();
        });
    }
};
