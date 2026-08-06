<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Permission groups mapped to each domain module.
     */
    public const GROUPS = [
        'dashboard' => ['view'],
        'master' => ['view', 'create', 'edit', 'delete'],
        'customer' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
        'supplier' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
        'product' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
        'stock' => ['view', 'adjust', 'opname', 'transfer', 'approve', 'post'],
        'purchase' => ['view', 'create', 'edit', 'delete', 'approve', 'receive', 'post', 'void'],
        'sale' => ['view', 'create', 'edit', 'delete', 'approve', 'post', 'void', 'convert'],
        'payment' => ['view', 'create', 'approve'],
        'report' => ['view', 'export'],
        'user' => ['view', 'create', 'edit', 'delete', 'assign_role'],
        'setting' => ['view', 'edit'],
    ];

    public function run(): void
    {
        foreach (self::GROUPS as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }
    }
}
