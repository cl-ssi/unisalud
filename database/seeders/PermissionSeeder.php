<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
//use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['name'=>'Administrator','description'=>'Administrador del sistema']);
        Permission::create(['name'=>'Developer','description'=>'Desarrollador']);

        // Permission::create(['name'=>'Mp:  administrator','description'=>'Administrador del programador médico']);
        Permission::create(['name' => 'Mp: administrador']);
        Permission::create(['name' => 'Mp: programacion teorica']);
        Permission::create(['name' => 'Mp: programacion medica']);
        Permission::create(['name' => 'Mp: programacion no medica']);
        Permission::create(['name' => 'Mp: programador pabellon']);
        Permission::create(['name' => 'Mp: programador']);
        Permission::create(['name' => 'Mp: reportes']);
        Permission::create(['name' => 'Mp: mantenedores']);

        Permission::create(['name'=>'Fq: admin','description'=>'Administrador de Fq']);
        Permission::create(['name'=>'Fq: answer request dispensing','description'=>'Atención de requerimientos de medicamentos']);
    }
}
