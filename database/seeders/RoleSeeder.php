<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Role -> permission pattern mapping.
     */
    public const ROLES = [
        'super admin' => '*',
        'manager' => [
            'dashboard.view', 'report.view', 'report.export',
            'master.view', 'customer.view', 'supplier.view', 'product.view',
            'stock.view', 'stock.approve', 'purchase.view', 'purchase.approve',
            'sale.view', 'sale.approve', 'payment.view', 'payment.approve',
            'purchase.receive', 'stock.post',
        ],
        'warehouse' => [
            'dashboard.view', 'product.view', 'stock.view',
            'stock.adjust', 'stock.opname', 'stock.transfer', 'stock.post',
            'purchase.receive', 'sale.view',
        ],
        'purchasing' => [
            'dashboard.view', 'product.view', 'supplier.view',
            'stock.view', 'purchase.view', 'purchase.create', 'purchase.edit',
            'purchase.receive', 'purchase.post', 'purchase.delete', 'report.view',
        ],
        'sales' => [
            'dashboard.view', 'product.view', 'customer.view',
            'stock.view', 'sale.view', 'sale.create', 'sale.edit',
            'sale.post', 'sale.convert', 'sale.delete', 'report.view',
        ],
        'finance' => [
            'dashboard.view', 'report.view', 'report.export',
            'payment.view', 'payment.create', 'payment.approve',
            'purchase.view', 'sale.view', 'stock.view',
        ],
        'viewer' => [
            'dashboard.view', 'report.view', 'report.export',
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($permissions === '*') {
                $role->syncPermissions(\Spatie\Permission\Models\Permission::all());
            } else {
                $role->syncPermissions($permissions);
            }
        }
    }
}
