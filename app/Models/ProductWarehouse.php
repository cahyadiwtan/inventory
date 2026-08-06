<?php

namespace App\Models;

class ProductWarehouse extends BaseModel
{
    protected $fillable = ['product_id', 'warehouse_id', 'qty_on_hand'];

    protected function casts(): array
    {
        return [
            'qty_on_hand' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
