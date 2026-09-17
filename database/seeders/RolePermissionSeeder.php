<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Mencipta semua peranan dan kebenaran, serta memetakannya.
 * Idempotent — selamat dijalankan berulang kali.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan cache permission Spatie.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Cipta semua permission.
        foreach (Permissions::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. Cipta semua peranan.
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        // 3. Petakan permission kepada peranan.
        foreach (Permissions::forRoles() as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
