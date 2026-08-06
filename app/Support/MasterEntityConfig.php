<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Warehouse;

class MasterEntityConfig
{
    /**
     * Registry of master data entities with their model, labels, and fields.
     */
    public const ENTITIES = [
        'categories' => [
            'model' => ProductCategory::class,
            'title' => 'Product Category',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
            ],
        ],
        'brands' => [
            'model' => ProductBrand::class,
            'title' => 'Product Brand',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
            ],
        ],
        'units' => [
            'model' => Unit::class,
            'title' => 'Unit',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'symbol' => ['label' => 'Symbol', 'type' => 'text', 'required' => true],
            ],
        ],
        'warehouses' => [
            'model' => Warehouse::class,
            'title' => 'Warehouse',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'address' => ['label' => 'Address', 'type' => 'textarea', 'required' => false],
                'phone' => ['label' => 'Phone', 'type' => 'text', 'required' => false],
            ],
        ],
        'taxes' => [
            'model' => Tax::class,
            'title' => 'Tax',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'rate' => ['label' => 'Rate (%)', 'type' => 'number', 'required' => true],
            ],
        ],
        'customers' => [
            'model' => Customer::class,
            'title' => 'Customer',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'npwp' => ['label' => 'NPWP', 'type' => 'text', 'required' => false],
                'address' => ['label' => 'Address', 'type' => 'textarea', 'required' => true],
                'phone' => ['label' => 'Phone', 'type' => 'text', 'required' => false],
                'email' => ['label' => 'Email', 'type' => 'email', 'required' => false],
                'pic_name' => ['label' => 'PIC', 'type' => 'text', 'required' => false],
                'payment_term_days' => ['label' => 'Payment Term (Days)', 'type' => 'number', 'required' => true, 'integer' => true, 'default' => 30],
                'credit_limit' => ['label' => 'Credit Limit', 'type' => 'number', 'required' => true, 'default' => 0],
            ],
        ],
        'suppliers' => [
            'model' => Supplier::class,
            'title' => 'Supplier',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'npwp' => ['label' => 'NPWP', 'type' => 'text', 'required' => false],
                'address' => ['label' => 'Address', 'type' => 'textarea', 'required' => true],
                'phone' => ['label' => 'Phone', 'type' => 'text', 'required' => false],
                'email' => ['label' => 'Email', 'type' => 'email', 'required' => false],
                'pic_name' => ['label' => 'PIC', 'type' => 'text', 'required' => false],
                'payment_term_days' => ['label' => 'Payment Term (Days)', 'type' => 'number', 'required' => true, 'integer' => true, 'default' => 30],
                'credit_limit' => ['label' => 'Credit Limit', 'type' => 'number', 'required' => true, 'default' => 0],
            ],
        ],
    ];

    public static function config(string $entity): array
    {
        if (! isset(self::ENTITIES[$entity])) {
            abort(404);
        }

        return self::ENTITIES[$entity];
    }
}
