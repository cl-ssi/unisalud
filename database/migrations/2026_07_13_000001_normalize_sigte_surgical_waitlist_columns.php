<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sigte_surgical_waitlists', function (Blueprint $table) {
            $table->timestamp('exported_at')->nullable()->after('status');
        });

        DB::table('sigte_surgical_waitlists')
            ->whereNotIn('status', ['completo', 'incompleto'])
            ->update(['status' => 'incompleto']);

        Schema::table('sigte_surgical_waitlists', function (Blueprint $table) {
            $table->string('status')->default('incompleto')->change();
        });

        // Give every row a simple sequential local id before the column is
        // retyped, since the existing values are non-numeric ("QX-...").
        DB::statement('UPDATE sigte_surgical_waitlists SET identifier = id');

        Schema::table('sigte_surgical_waitlists', function (Blueprint $table) {
            $table->unsignedInteger('identifier')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sigte_surgical_waitlists', function (Blueprint $table) {
            $table->string('identifier')->nullable()->change();
            $table->string('status')->default('ingresado')->change();
            $table->dropColumn('exported_at');
        });
    }
};
