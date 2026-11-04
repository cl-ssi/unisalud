<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * From MINSAL's "Códigos de especialidad" (Hoja4: 07-xxx, Hoja5: 09-xxx):
     * the old Presta_Min code each specialty replaced (shared by several
     * specialties, e.g. adult and pediatric) and the patient age range, in
     * years, SIGTE accepts for it. Also adds the 15 codes missing from the
     * catalog; existing names are kept.
     */
    public function up(): void
    {
        Schema::table('minsal_specialties', function (Blueprint $table) {
            $table->string('old_code')->nullable()->after('code')->index();
            $table->decimal('min_age', 5, 2)->nullable()->after('text');
            $table->decimal('max_age', 5, 2)->nullable()->after('min_age');
        });

        foreach ($this->specialties() as $code => [$text, $oldCode, $minAge, $maxAge]) {
            $attributes = ['old_code' => $oldCode, 'min_age' => $minAge, 'max_age' => $maxAge, 'updated_at' => now()];

            if (DB::table('minsal_specialties')->where('code', $code)->exists()) {
                DB::table('minsal_specialties')->where('code', $code)->update($attributes);
            } else {
                DB::table('minsal_specialties')->insert($attributes + ['code' => $code, 'text' => $text, 'created_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        DB::table('minsal_specialties')->whereIn('code', [
            '07-001', '07-024', '07-027', '07-031', '07-032', '07-033', '07-034', '07-035',
            '07-036', '07-050', '07-052', '07-101', '09-006', '09-012', '09-013',
        ])->delete();

        Schema::table('minsal_specialties', function (Blueprint $table) {
            $table->dropColumn(['old_code', 'min_age', 'max_age']);
        });
    }

    /**
     * @return array<string, array{string, ?string, int|float|null, int|float|null}> code => [text, old_code, min_age, max_age]
     */
    private function specialties(): array
    {
        return [
            '07-001' => ['Anatomía Patológica', null, 0, 150],
            '07-002' => ['Anestesiología', '07-210-0', 0, 150],
            '07-003' => ['Cardiología', '07-103-0', 15, 150],
            '07-004' => ['Cirugía General', '07-200-2', 15, 150],
            '07-005' => ['Cirugía De Cabeza, Cuello Y Maxilofacial', '07-203-0', 15, 150],
            '07-006' => ['Cirugía Cardiovascular', '07-209-0', 0, 150],
            '07-007' => ['Cirugía De Tórax', '07-206-0', 15, 150],
            '07-008' => ['Cirugía Plástica Y Reparadora', '07-204-0', 0, 150],
            '07-009' => ['Cirugía Pediátrica', '07-200-1', 0, 15],
            '07-010' => ['Cirugía Vascular Periférica', '07-207-2', 15, 150],
            '07-011' => ['Coloproctología', '07-205-2', 15, 150],
            '07-012' => ['Dermatología', '07-111-0', 0, 150],
            '07-013' => ['Diabetología', '07-104-0', 0, 150],
            '07-014' => ['Endocrinología Adulto', '07-104-0', 15, 150],
            '07-015' => ['Endocrinología Pediátrica', '07-104-0', 0, 15],
            '07-016' => ['Enfermedades Respiratorias Del Adulto (Broncopulmonar)', '07-102-0', 15, 150],
            '07-017' => ['Enfermedades Respiratorias Pediátricas (Broncopulmonar Pediatrico)', '07-102-0', 0, 15],
            '07-018' => ['Gastroenterología Adulto', '07-105-0', 15, 150],
            '07-019' => ['Gastroenterología Pediátrica', '07-105-0', 0, 15],
            '07-020' => ['Genética Clínica', '07-106-0', 0, 150],
            '07-021' => ['Geriatría', '07-113-2', 60, 150],
            '07-022' => ['Ginecología Pediátrica Y De La Adolescencia', '07-301-0', 0, 18],
            '07-023' => ['Hematología', '07-107-0', 15, 150],
            '07-024' => ['Imagenología', null, 0, 150],
            '07-025' => ['Infectología', '07-118-0', 0, 150],
            '07-026' => ['Inmunología', '07-110-2', 0, 150],
            '07-027' => ['Laboratorio Clínico', null, 0, 150],
            '07-028' => ['Medicina Familiar', '07-900-1', 0, 150],
            '07-029' => ['Medicina Física Y Rehabilitación (Fisiatria Adulto)', '07-114-0', 0, 150],
            '07-030' => ['Medicina Interna', '07-100-2', 15, 150],
            '07-031' => ['Medicina Intensiva Adulto', null, 0, 150],
            '07-032' => ['Medicina Intensiva Pediátrica', null, 0, 15],
            '07-033' => ['Medicina Legal', null, 0, 150],
            '07-034' => ['Medicina Materno Infantil', null, 0, 50],
            '07-035' => ['Medicina Nuclear', null, 0, 150],
            '07-036' => ['Medicina De Urgencia', null, 0, 150],
            '07-037' => ['Nefrología Adulto', '07-108-0', 15, 150],
            '07-038' => ['Nefrología Pediátrico', '07-108-0', 0, 15],
            '07-039' => ['Neonatología', '07-101-1', 0, 0.2],
            '07-040' => ['Neurocirugía', '07-208-0', 0, 150],
            '07-041' => ['Neurología Adulto', '07-115-0', 15, 150],
            '07-042' => ['Neurología Pediátrica', '07-115-0', 0, 15],
            '07-043' => ['Obstetricia Y Ginecología', '07-300-1', 9, 150],
            '07-044' => ['Oftalmología', '07-400-9', 0, 150],
            '07-045' => ['Oncología Médica', '07-116-0', 0, 150],
            '07-046' => ['Otorrinolaringología', '07-500-9', 0, 150],
            '07-047' => ['Pediatría', '07-100-1', 0, 15],
            '07-048' => ['Psiquiatría Adulto', '07-117-0', 15, 150],
            '07-049' => ['Psiquiatría Pediátrica Y De La Adolescencia', '07-117-0', 0, 18],
            '07-050' => ['Radioterapia Oncológica', '07-116-0', 0, 150],
            '07-051' => ['Reumatología', '07-110-2', 15, 150],
            '07-052' => ['Salud Pública', null, 0, 150],
            '07-053' => ['Traumatología Y Ortopedia', '07-700-0', 15, 150],
            '07-054' => ['Urología', '07-800-0', 15, 150],
            '07-055' => ['Cardiología Pediátrica', '07-103-0', 0, 15],
            '07-056' => ['Cirugía Digestiva', '07-201-0', 15, 150],
            '07-057' => ['Cirugía Plastica Y Reparadora Pediátrica', '07-204-0', 0, 15],
            '07-058' => ['Ginecología', '07-301-0', 15, 150],
            '07-059' => ['Hemato-Oncología Pediátrica', '07-107-0', 0, 15],
            '07-060' => ['Infectología Pediatrica', '07-118-0', 0, 15],
            '07-061' => ['Medicina Familiar Del Niño', '07-900-1', 0, 15],
            '07-062' => ['Medicina Fisica Y Rehabilitación Pediátrica (Fisiatria Pediatrica)', '07-114-0', 0, 15],
            '07-063' => ['Nutriólogo', '07-109-0', 0, 150],
            '07-064' => ['Nutriólogo Pediátrico', '07-109-0', 0, 15],
            '07-065' => ['Reumatología Pediátrica', '07-110-2', 0, 15],
            '07-066' => ['Obstetricia', '07-300-1', 9, 50],
            '07-067' => ['Traumatología Y Ortopedia Pediátrica', '07-700-0', 0, 15],
            '07-068' => ['Urología Pediátrica', '07-800-0', 0, 15],
            '07-100' => ['Cirugía De Mama', '07-202-2', 0, 150],
            '07-101' => ['Enf. Trasmisión Sexual', '07-112-0', 0, 150],
            '07-102' => ['Salud Ocupacional', '07-600-9', 0, 150],
            '09-001' => ['Cirugía Y Traumatología Buco Maxilofacial', null, 0, 150],
            '09-002' => ['Cirugía Bucal', null, 0, 150],
            '09-003' => ['Endodoncia', null, 0, 150],
            '09-004' => ['Imagenología Oral Y Maxilofacial', null, 0, 150],
            '09-005' => ['Implantologia Buco Maxilofacial', null, 0, 150],
            '09-006' => ['Odontología Legal', null, null, null],
            '09-007' => ['Odontopediatría', null, 0, 15],
            '09-008' => ['Ortodoncia Y Ortopedia Dento Máxilo Facial', null, 0, 150],
            '09-009' => ['Patología Oral', null, 0, 150],
            '09-010' => ['Periodoncia', null, 0, 150],
            '09-030' => ['Rehabilitación Oral', null, 15, 150],
            '09-011' => ['Rehabilitación Oral', null, 15, 150],
            '09-012' => ['Salud Pública', null, null, null],
            '09-013' => ['Somato-Prótesis', null, null, null],
            '09-014' => ['Trastornos Temporomandibulares Y Dolor Orofacial', null, 0, 150],
        ];
    }
};
