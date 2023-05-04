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
        Permission::create(['name' => 'Administrator','description'=>'Administrador del sistema']);
        Permission::create(['name' => 'Developer','description'=>'Desarrollador']);
        Permission::create(['name' => 'be god','description'=>'GOD MODE']);

        Permission::create(['name' => 'Epi: Add Value','description'=>'Permite añadir datos a caso sospecha']);
        Permission::create(['name' => 'Epi: Create','description'=>'Permite crear casos sospecha']);
        
        Permission::create(['name' => 'Mp: perfil ficha de programacion']);
        Permission::create(['name' => 'Mp: perfil jefe de unidad']);
        Permission::create(['name' => 'Mp: perfil referente programacion']);
        Permission::create(['name' => 'Mp: perfil rrhh']);
        Permission::create(['name' => 'Mp: perfil administrador']);

        Permission::create(['name' => 'Fq: admin','description' => 'Administrador de Fq']);
        Permission::create(['name' => 'Fq: answer request dispensing','description' => 'Atención de requerimientos de medicamentos']);

        Permission::create(['name' => 'SAMU', 'description' => 'Permite acceder al módulo SAMU']);
        Permission::create(['name' => 'SAMU tripulación', 'description' => 'Aparece en el listado para asignar a tripulación']);
        Permission::create(['name' => 'SAMU operador', 'description' => 'Funciones de operador para SAMU']);
        Permission::create(['name' => 'SAMU despachador', 'description' => 'Funciones de despachador para SAMU']);
        Permission::create(['name' => 'SAMU regulador', 'description' => 'Funciones de regulador para SAMU']);
        Permission::create(['name' => 'SAMU conductor', 'description' => 'Función de conductor para SAMU']);
        Permission::create(['name' => 'SAMU administrador', 'description' => 'Función de administrador para SAMU']);
        Permission::create(['name' => 'SAMU auditor', 'description' => 'Función de auditor para SAMU, no puede modificar nada']);

        Permission::create(['name' => 'Some: user', 'description' => 'Permite acceso al módulo de SOME']);

        /*
        php artisan permission:cache-reset
        */
    }
}
