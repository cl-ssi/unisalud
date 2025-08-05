<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('condition')->insert(
            [
                [
                    'name' => 'electrodependencia',
                    'code' => 'ELECTRO',
                    'description' => 'Condición de salud que requiere el uso de dispositivos médicos eléctricos para la vida diaria.',
                    'risk' => 'alto',
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'movilidad reducida',
                    'code' => 'MOVIRED',
                    'description' => 'Condición de salud que limita la capacidad de movimiento.',
                    'risk' => 'medio',
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'oxigeno dependiente',
                    'code' => 'OXIDEP',
                    'description' => 'Condición de salud que requiere el uso continuo de oxígeno.',
                    'risk' => 'alto',
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'alimentacion enteral',
                    'code' => 'ENTERAL',
                    'description' => 'Condición de salud que requiere alimentación a través de sondas.',
                    'risk' => 'alto',
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'oncologicos',
                    'code' => 'ONCO',
                    'description' => 'Condición de salud relacionada con enfermedades oncológicas.',
                    'risk' => 'alto',
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'cuidados paliativos universales',
                    'code' => 'CPU',
                    'description' => 'Condición de salud que requiere cuidados paliativos para mejorar la calidad de vida.',
                    'risk' => 'alto',
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'naneas',
                    'code' => 'NANEAS',
                    'description' => 'Condición de salud que afecta a niños, niñas y adolescentes.',
                    'risk' => 'medio',
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'asistencia ventilatoria no invasiva',
                    'code' => 'AVNI',
                    'description' => 'Condición de salud que requiere asistencia ventilatoria no invasiva.',
                    'risk' => 'alto',
                    'parent_id' => 1, // Assuming the ID of electrodependencia is 1
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'asistencia ventilatoria invasiva',
                    'code' => 'AVI',
                    'description' => 'Condición de salud que requiere asistencia ventilatoria invasiva.',
                    'risk' => 'alto',
                    'parent_id' => 1, // Assuming the ID of electrodependencia is 1
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'concentradores de oxigeno',
                    'code' => 'CONOXI',
                    'description' => 'Condición de salud que requiere el uso de concentradores de oxígeno.',
                    'risk' => 'alto',
                    'parent_id' => 1, // Assuming the ID of electrodependencia is 1
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]
        );
    }
}
