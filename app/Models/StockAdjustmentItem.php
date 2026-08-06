<?php

namespace App\Models;

class StockAdjustmentItem extends BaseModel
{
    protected $fillable = ['stock_adjustment_id', 'product_id', 'qty'];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
        ];
    }

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
