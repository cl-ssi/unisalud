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
        foreach ($this->specialties() as $code => $text) {
            DB::table('minsal_specialties')->updateOrInsert(
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

    private function specialties(): array
    {
        return [
            '07-002' => 'Anestesiología',
            '07-003' => 'Cardiología',
            '07-055' => 'Cardiología Pediátrica',
            '09-002' => 'Cirugía Bucal',
            '07-006' => 'Cirugía Cardiovascular',
            '07-005' => 'Cirugía De Cabeza, Cuello Y Maxilofacial',
            '07-100' => 'Cirugía De Mama',
            '07-007' => 'Cirugía De Tórax',
            '07-056' => 'Cirugía Digestiva',
            '07-004' => 'Cirugía General',
            '07-009' => 'Cirugía Pediátrica',
            '07-008' => 'Cirugía Plástica Y Reparadora',
            '07-057' => 'Cirugía Plastica Y Reparadora Pediátrica',
            '07-010' => 'Cirugía Vascular Periférica',
            '09-001' => 'Cirugía Y Traumatología Buco Maxilofacial',
            '07-011' => 'Coloproctología',
            '07-012' => 'Dermatología',
            '07-013' => 'Diabetología',
            '07-014' => 'Endocrinología Adulto',
            '07-015' => 'Endocrinología Pediátrica',
            '09-003' => 'Endodoncia',
            '07-016' => 'Enfermedades Respiratorias Del Adulto (Broncopulmonar)',
            '07-017' => 'Enfermedades Respiratorias Pediátricas (Broncopulmonar Pediatrico)',
            '07-018' => 'Gastroenterología Adulto',
            '07-019' => 'Gastroenterología Pediátrica',
            '07-020' => 'Genética Clínica',
            '07-021' => 'Geriatría',
            '07-058' => 'Ginecología',
            '07-022' => 'Ginecología Pediátrica Y De La Adolescencia',
            '07-023' => 'Hematología',
            '07-059' => 'Hemato-Oncología Pediátrica',
            '09-004' => 'Imagenología Oral Y Maxilofacial',
            '09-005' => 'Implantologia Buco Maxilofacial',
            '07-025' => 'Infectología',
            '07-060' => 'Infectología Pediatrica',
            '07-026' => 'Inmunología',
            '07-028' => 'Medicina Familiar',
            '07-061' => 'Medicina Familiar Del Niño',
            '07-029' => 'Medicina Física Y Rehabilitación (Fisiatria Adulto)',
            '07-062' => 'Medicina Fisica Y Rehabilitación Pediátrica (Fisiatria Pediatrica)',
            '07-030' => 'Medicina Interna',
            '07-037' => 'Nefrología Adulto',
            '07-038' => 'Nefrología Pediátrico',
            '07-039' => 'Neonatología',
            '07-040' => 'Neurocirugía',
            '07-041' => 'Neurología Adulto',
            '07-042' => 'Neurología Pediátrica',
            '07-063' => 'Nutriólogo',
            '07-064' => 'Nutriólogo Pediátrico',
            '07-066' => 'Obstetricia',
            '07-043' => 'Obstetricia Y Ginecología',
            '09-007' => 'Odontopediatría',
            '07-044' => 'Oftalmología',
            '07-045' => 'Oncología Médica',
            '09-008' => 'Ortodoncia Y Ortopedia Dento Máxilo Facial',
            '07-046' => 'Otorrinolaringología',
            '09-009' => 'Patología Oral',
            '07-047' => 'Pediatría',
            '09-010' => 'Periodoncia',
            '07-048' => 'Psiquiatría Adulto',
            '07-049' => 'Psiquiatría Pediátrica Y De La Adolescencia',
            '09-030' => 'Rehabilitación Oral Fija',
            '09-011' => 'Rehabilitación Oral Removible',
            '07-051' => 'Reumatología',
            '07-065' => 'Reumatología Pediátrica',
            '07-102' => 'Salud Ocupacional',
            '09-014' => 'Trastornos Temporomandibulares Y Dolor Orofacial',
            '07-053' => 'Traumatología Y Ortopedia',
            '07-067' => 'Traumatología Y Ortopedia Pediátrica',
            '07-054' => 'Urología',
            '07-068' => 'Urología Pediátrica',
        ];
    }
};
