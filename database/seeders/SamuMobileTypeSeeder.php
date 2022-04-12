<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Samu\MobileType;

class SamuMobileTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MobileType::create([
            'name' => 'M1',
            'description' => 'Básica: conductor y técnico paramédico.',
            'valid_from' => '2022-01-01 00:00:00',
            'valid_to' => '2025-01-01 00:00:00',
            'status' => true,
        ]);
        MobileType::create([
            'name' => 'M2',
            'description' => 'Avanzada: conductor, técnico paramédico y reanimador',
            'valid_from' => '2022-01-01 00:00:00',
            'valid_to' => '2025-01-01 00:00:00',
            'status' => true,
        ]);
        MobileType::create([
            'name' => 'M3',
            'description' => 'Avanzada + médico: conductor, técnico paramédico y médico',
            'valid_from' => '2022-01-01 00:00:00',
            'valid_to' => '2025-01-01 00:00:00',
            'status' => true,
        ]);
        MobileType::create([
            'name' => 'Hibrido',
            'description' => 'Conductor y 2 técnicos paramédicos.',
            'valid_from' => '2022-01-01 00:00:00',
            'valid_to' => '2025-01-01 00:00:00',
            'status' => true,
        ]);
        MobileType::create([
            'name' => 'RU1',
            'description' => 'Rural Básica: conductor y técnico paramédico.',
            'valid_from' => '2022-01-01 00:00:00',
            'valid_to' => '2025-01-01 00:00:00',
            'status' => true,
        ]);
        MobileType::create([
            'name' => 'RU2',
            'description' => 'Rural Avanzada: conductor, técnico paramédico y reanimador',
            'valid_from' => '2022-01-01 00:00:00',
            'valid_to' => '2025-01-01 00:00:00',
            'status' => true,
        ]);
    }
}
