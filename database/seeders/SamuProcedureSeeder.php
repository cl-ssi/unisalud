<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Samu\Procedure;

class SamuProcedureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Procedure::create([
            'code'=>'1707017',
            'name'=>'CAPNOGRAFÍA',
            'valid_from'=>'2022-01-01',
            'value'=>15000,
        ]);
        Procedure::create([
            'code'=>'1701034',
            'name'=>'CARDIOVERSIÓN',
            'valid_from'=>'2022-01-01',
            'value'=>25000,   
        ]);
        Procedure::create([
            'code'=>'1701009',
            'name'=>'CONTROL SIGNOS VITALES',
            'valid_from'=>'2022-01-01',
            'value'=>3000,
        ]);
    }
}
