<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Samu\SupplyCategory;

class SamuSupplyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SupplyCategory::create([
            'name' => 'Medicamento'
        ]);
        SupplyCategory::create([
            'name' => 'Insumo'
        ]);
    }
}
