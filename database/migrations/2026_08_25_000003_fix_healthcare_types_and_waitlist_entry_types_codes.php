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
        // PREVISION codes from the SIGTE export.
        foreach ($this->healthcareTypes() as $code => $text) {
            DB::table('healthcare_types')->updateOrInsert(
                ['code' => $code],
                ['text' => $text, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // TIPO_PREST codes from the SIGTE export.
        foreach ($this->waitlistEntryTypes() as $code => $text) {
            DB::table('waitlist_entry_types')->updateOrInsert(
                ['code' => $code],
                ['text' => $text, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function healthcareTypes(): array
    {
        return [
            '1'  => 'Fonasa',
            '2'  => 'Isapre',
            '3'  => 'Capradena',
            '4'  => 'Dipreca',
            '5'  => 'Sisa',
            '96' => 'Ninguna',
            '99' => 'Desconocido',
        ];
    }

    private function waitlistEntryTypes(): array
    {
        return [
            '1' => 'Consulta Nueva Especialidad',
            '3' => 'Procedimiento',
            '4' => 'Intervención Quirúrgica',
        ];
    }
};
