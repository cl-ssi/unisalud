<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private string $roleName = 'Gestión Demanda Municipalidad';

    private array $permissionNames = [
        'DemandManagementWaitlist: View',
        'DemandManagementWaitlist: Edit',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = collect($this->permissionNames)
            ->mapWithKeys(fn ($name) => [$name => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])]);

        $role = Role::firstOrCreate(['name' => $this->roleName, 'guard_name' => 'web']);

        $role->givePermissionTo($permissions['DemandManagementWaitlist: View']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', $this->roleName)->delete();
        DB::table('permissions')->whereIn('name', $this->permissionNames)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
