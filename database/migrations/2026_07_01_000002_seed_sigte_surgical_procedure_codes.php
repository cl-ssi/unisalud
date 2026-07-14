<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $codes = [
            // ── Cirugía Mayor ──────────────────────────────────────────────────
            ['cirugia_mayor', '01-01-2553', 'Sutura intraoral'],
            ['cirugia_mayor', '01-01-2554', 'Tratamiento quirúrgico pseudoquiste y quiste odontológico'],
            ['cirugia_mayor', '01-01-2555', 'Tratamiento quirúrgico tumor odontológico'],
            ['cirugia_mayor', '01-01-2561', 'Cirugía ortognática'],
            ['cirugia_mayor', '21-03-313',  'Intervención quirúrgica integral Escoliosis Neuromuscular'],
            ['cirugia_mayor', '27-03-002',  'Corticotomía'],
            ['cirugia_mayor', '27-03-003',  'Disyunción palatina quirúrgica'],
            ['cirugia_mayor', '27-03-004',  'Extirpación de pseudoquistes, quistes y tumores'],
            ['cirugia_mayor', '27-03-005',  'Glosectomías'],
            ['cirugia_mayor', '27-03-007',  'Implantes subperiósticos'],
            ['cirugia_mayor', '27-03-009',  'Injertos en boca'],
            ['cirugia_mayor', '27-03-010',  'Intervenciones quirúrgicas en el seno maxilar'],
            ['cirugia_mayor', '27-03-013',  'Profundización de vestíbulo o reconstrucción de rebordes, con o sin injerto'],
            ['cirugia_mayor', '27-03-019',  'Tratamiento quirúrgico fracturas maxilar superior'],
            ['cirugia_mayor', '27-03-020',  'Tratamiento quirúrgico de fracturas en maxilar inferior'],
            ['cirugia_mayor', '27-03-023',  'Implante endo-oseo oseointegrable'],
            ['cirugia_mayor', '27-03-024',  'Pilar Protésico sobre Implantes'],
            ['cirugia_mayor', '27-05-003',  'Corticotomía'],
            ['cirugia_mayor', '27-05-004',  'Disyunción palatina quirúrgica'],
            ['cirugia_mayor', '27-05-005',  'Extirpación de pseudoquistes, quistes y tumores'],
            ['cirugia_mayor', '27-05-006',  'Glosectomías'],
            ['cirugia_mayor', '27-05-008',  'Implantes subperiósticos'],
            ['cirugia_mayor', '27-05-011',  'Injertos en boca'],
            ['cirugia_mayor', '27-05-012',  'Elevación de piso del seno maxilar'],
            ['cirugia_mayor', '27-05-015',  'Profundización de vestíbulo o reconstrucción de rebordes, con o sin injerto'],
            ['cirugia_mayor', '27-05-021',  'Tratamiento quirúrgico fracturas maxilar superior'],
            ['cirugia_mayor', '27-05-022',  'Tratamiento quirúrgico de fracturas en maxilar inferior'],
            ['cirugia_mayor', '27-05-025',  'Implante oseointegrado'],
            ['cirugia_mayor', '27-05-026',  'Pilar Protésico sobre Implantes'],
            ['cirugia_mayor', '50-99-022',  'Frenectomía'],
            ['cirugia_mayor', '50-99-023',  'Fenestración'],
            ['cirugia_mayor', '50-99-024',  'Drenaje Absceso intra-extraoral'],
            ['cirugia_mayor', '50-99-025',  'Cirugía preprotésica'],
            ['cirugia_mayor', '50-99-056',  'Desobturación de conductos'],

            // ── Cirugía Menor ──────────────────────────────────────────────────
            ['cirugia_menor', '27-03-001',  'Cirugía de enfermedad periodontal (por grupo)'],
            ['cirugia_menor', '27-03-006',  'Implante endodóntico intraóseo'],
            ['cirugia_menor', '27-03-008',  'Inclusiones dentarias (extracción de 3° Molar)'],
            ['cirugia_menor', '27-03-011',  'Plastía de fístula salival'],
            ['cirugia_menor', '27-03-012',  'Preparación quirúrgica de los maxilares con fines protésicos'],
            ['cirugia_menor', '27-03-014',  'Reimplante y trasplante dentario'],
            ['cirugia_menor', '27-03-015',  'Remoción de cuerpo extraño y secuestrectomía'],
            ['cirugia_menor', '27-03-016',  'Sutura completa de herida mayor'],
            ['cirugia_menor', '27-03-017',  'Sutura completa de herida menor'],
            ['cirugia_menor', '27-03-018',  'Sutura simple de herida'],
            ['cirugia_menor', '27-03-XXX',  'Odontología (general)'],
            ['cirugia_menor', '27-05-001',  'Cirugía bucal'],
            ['cirugia_menor', '27-05-002',  'Cirugía de enfermedad periodontal (por grupo)'],
            ['cirugia_menor', '27-05-007',  'Implante endodóntico intraóseo'],
            ['cirugia_menor', '27-05-009',  'Exodoncia de dientes retenidos'],
            ['cirugia_menor', '27-05-010',  'Exodoncia de tercer molar con osteotomía'],
            ['cirugia_menor', '27-05-013',  'Plastía de fístula salival'],
            ['cirugia_menor', '27-05-014',  'Preparación quirúrgica de los maxilares con fines protésicos'],
            ['cirugia_menor', '27-05-016',  'Reimplante y trasplante dentario'],
            ['cirugia_menor', '27-05-017',  'Remoción de cuerpo extraño y secuestrectomía'],
            ['cirugia_menor', '27-05-018',  'Sutura completa de herida mayor'],
            ['cirugia_menor', '27-05-019',  'Sutura completa de herida menor'],
            ['cirugia_menor', '27-05-020',  'Sutura simple de herida'],

            // ── Procedimiento ──────────────────────────────────────────────────
            ['procedimiento', '27-03-021',  'Tratamiento de traumatismo dento alveolar simple'],
            ['procedimiento', '27-03-022',  'Tratamiento de traumatismo dento alveolar complejo'],
        ];

        foreach ($codes as [$complexity, $code, $text]) {
            DB::table('sigte_surgical_procedure_codes')->updateOrInsert(
                ['complexity' => $complexity, 'code' => $code],
                ['text' => $text, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('sigte_surgical_procedure_codes')->whereIn('code', [
            '01-01-2553','01-01-2554','01-01-2555','01-01-2561','21-03-313',
            '27-03-001','27-03-002','27-03-003','27-03-004','27-03-005',
            '27-03-006','27-03-007','27-03-008','27-03-009','27-03-010',
            '27-03-011','27-03-012','27-03-013','27-03-014','27-03-015',
            '27-03-016','27-03-017','27-03-018','27-03-019','27-03-020',
            '27-03-021','27-03-022','27-03-023','27-03-024','27-03-XXX',
            '27-05-001','27-05-002','27-05-003','27-05-004','27-05-005',
            '27-05-006','27-05-007','27-05-008','27-05-009','27-05-010',
            '27-05-011','27-05-012','27-05-013','27-05-014','27-05-015',
            '27-05-016','27-05-017','27-05-018','27-05-019','27-05-020',
            '27-05-021','27-05-022','27-05-025','27-05-026',
            '50-99-022','50-99-023','50-99-024','50-99-025','50-99-056',
        ])->delete();
    }
};
