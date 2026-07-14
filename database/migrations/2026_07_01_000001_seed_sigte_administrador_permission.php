<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'SIGTE LE QX: administrador', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('name', 'SIGTE LE QX: administrador')
            ->where('guard_name', 'web')
            ->delete();
    }
};
