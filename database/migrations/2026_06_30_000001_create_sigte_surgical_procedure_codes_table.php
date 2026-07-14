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
        Schema::create('sigte_surgical_procedure_codes', function (Blueprint $table) {
            $table->id();

            $table->string('complexity')->nullable(); // baja - mediana - alta
            $table->string('code')->nullable();        // PRESTA_MIN
            $table->string('text')->nullable();         // PRESTA_EST

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sigte_surgical_procedure_codes');
    }
};
