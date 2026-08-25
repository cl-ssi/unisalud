<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private string $roleName = 'Odontología Municipalidad';

    private string $permissionName = 'OdontologyWaitlist: View';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = Role::firstOrCreate(['name' => $this->roleName, 'guard_name' => 'web']);

        $role->givePermissionTo($this->permissionName);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', $this->roleName)->delete();
    }
};
