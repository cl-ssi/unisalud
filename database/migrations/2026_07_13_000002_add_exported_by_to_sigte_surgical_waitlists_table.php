<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sigte_surgical_waitlists', function (Blueprint $table) {
            $table->foreignId('exported_by')->nullable()->after('exported_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sigte_surgical_waitlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exported_by');
        });
    }
};
