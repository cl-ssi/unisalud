<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sigte_surgical_exports', function (Blueprint $table) {
            $table->foreignId('requesting_professional_id')->nullable()->after('hasta')->constrained('users')->nullOnDelete();
            $table->string('status')->nullable()->after('requesting_professional_id');
            $table->string('complexity')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('sigte_surgical_exports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requesting_professional_id');
            $table->dropColumn(['status', 'complexity']);
        });
    }
};
