<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Warehouse;

class DataTransferConfig
{
    /**
     * Registry of importable/exportable entities.
     *
     * field => ['label', 'type' => 'text|number|date|boolean', 'lookup' => [Model, codeField], 'required']
     */
    public const ENTITIES = [
        'categories' => [
            'model' => ProductCategory::class,
            'title' => 'Kategori',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'is_active' => ['label' => 'Active (1/0)', 'type' => 'boolean'],
            ],
        ],
        'brands' => [
            'model' => ProductBrand::class,
            'title' => 'Merek',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'is_active' => ['label' => 'Active (1/0)', 'type' => 'boolean'],
            ],
        ],
        'units' => [
            'model' => Unit::class,
            'title' => 'Satuan',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'symbol' => ['label' => 'Symbol', 'type' => 'text'],
                'is_active' => ['label' => 'Active (1/0)', 'type' => 'boolean'],
            ],
        ],
        'warehouses' => [
            'model' => Warehouse::class,
            'title' => 'Gudang',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'address' => ['label' => 'Address', 'type' => 'text'],
                'phone' => ['label' => 'Phone', 'type' => 'text'],
                'is_active' => ['label' => 'Active (1/0)', 'type' => 'boolean'],
            ],
        ],
        'taxes' => [
            'model' => Tax::class,
            'title' => 'Pajak',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'rate' => ['label' => 'Rate (%)', 'type' => 'number', 'required' => true],
                'is_active' => ['label' => 'Active (1/0)', 'type' => 'boolean'],
            ],
        ],
        'customers' => [
            'model' => Customer::class,
            'title' => 'Customer',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'npwp' => ['label' => 'NPWP', 'type' => 'text'],
                'address' => ['label' => 'Address', 'type' => 'text'],
                'phone' => ['label' => 'Phone', 'type' => 'text'],
                'email' => ['label' => 'Email', 'type' => 'text'],
                'pic_name' => ['label' => 'PIC', 'type' => 'text'],
                'payment_term_days' => ['label' => 'Payment Term (Days)', 'type' => 'number'],
                'credit_limit' => ['label' => 'Credit Limit', 'type' => 'number'],
                'is_active' => ['label' => 'Active (1/0)', 'type' => 'boolean'],
            ],
        ],
        'suppliers' => [
            'model' => Supplier::class,
            'title' => 'Supplier',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'npwp' => ['label' => 'NPWP', 'type' => 'text'],
                'address' => ['label' => 'Address', 'type' => 'text'],
                'phone' => ['label' => 'Phone', 'type' => 'text'],
                'email' => ['label' => 'Email', 'type' => 'text'],
                'pic_name' => ['label' => 'PIC', 'type' => 'text'],
                'payment_term_days' => ['label' => 'Payment Term (Days)', 'type' => 'number'],
                'credit_limit' => ['label' => 'Credit Limit', 'type' => 'number'],
                'is_active' => ['label' => 'Active (1/0)', 'type' => 'boolean'],
            ],
        ],
        'products' => [
            'model' => Product::class,
            'title' => 'Produk',
            'fields' => [
                'code' => ['label' => 'Code', 'type' => 'text', 'required' => true],
                'barcode' => ['label' => 'Barcode', 'type' => 'text'],
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'category_code' => ['label' => 'Category Code', 'type' => 'text', 'lookup' => [ProductCategory::class, 'code']],
                'brand_code' => ['label' => 'Brand Code', 'type' => 'text', 'lookup' => [ProductBrand::class, 'code']],
                'unit_code' => ['label' => 'Unit Code', 'type' => 'text', 'lookup' => [Unit::class, 'code'], 'required' => true],
                'selling_price' => ['label' => 'Selling Price', 'type' => 'number'],
                'purchase_price' => ['label' => 'Purchase Price', 'type' => 'number'],
                'min_stock' => ['label' => 'Min Stock', 'type' => 'number'],
                'max_stock' => ['label' => 'Max Stock', 'type' => 'number'],
                'reorder_point' => ['label' => 'Reorder Point', 'type' => 'number'],
                'weight' => ['label' => 'Weight', 'type' => 'number'],
                'is_active' => ['label' => 'Active (1/0)', 'type' => 'boolean'],
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

    public static function headings(string $entity): array
    {
        return array_map(fn ($meta) => $meta['label'], self::config($entity)['fields']);
    }
}