<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RUNs that have appeared in an uploaded "LE CNE" file. Those rows are
     * upserted into users/identifiers, which also hold everyone else in the
     * system, so this keeps track of which RUNs actually came from LE CNE.
     * Cumulative, like the patient upsert itself.
     */
    public function up(): void
    {
        Schema::create('sigte_lecne_runs', function (Blueprint $table) {
            $table->id();
            $table->string('run')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sigte_lecne_runs');
    }
};
