<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Users are looked up by RUN (identifiers.value) all over the app, e.g.
     * once per row by OdontologyWaitlistImport; without an index each
     * lookup scans every identifier.
     */
    public function up(): void
    {
        Schema::table('identifiers', function (Blueprint $table) {
            $table->index(['value', 'cod_con_identifier_type_id'], 'identifiers_value_type_index');
        });
    }

    public function down(): void
    {
        Schema::table('identifiers', function (Blueprint $table) {
            $table->dropIndex('identifiers_value_type_index');
        });
    }
};
