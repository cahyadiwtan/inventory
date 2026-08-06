<?php

namespace App\Models;

class Product extends BaseModel
{
    protected $fillable = [
        'code',
        'barcode',
        'name',
        'category_id',
        'brand_id',
        'unit_id',
        'selling_price',
        'purchase_price',
        'min_stock',
        'max_stock',
        'reorder_point',
        'weight',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'min_stock' => 'decimal:2',
            'max_stock' => 'decimal:2',
            'reorder_point' => 'decimal:2',
            'weight' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(ProductBrand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function warehouses()
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Total stock across all warehouses.
     */
    public function totalStock(): float
    {
        return (float) $this->warehouses()->sum('qty_on_hand');
    }
}
