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
        Schema::create('condition', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('code')->nullable();
            
            $table->string('description')->nullable();

            $table->string('risk')->nullable();

            $table->foreignId('parent_id')->nullable()->constrained('condition', 'id')->onDelete('cascade');

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('condition', function (Blueprint $table) {
            // Eliminar la restricción de clave foránea y la columna
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
        Schema::dropIfExists('condition');
    }
};
