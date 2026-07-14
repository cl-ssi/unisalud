<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->permissions() as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', $this->permissions())
            ->where('guard_name', 'web')
            ->delete();
    }

    private function permissions(): array
    {
        return [
            'SIGTE LE QX: ingresar paciente',
            'SIGTE LE QX: listado',
        ];
    }
};
