<?php

namespace App\Models;

class StockOpnameItem extends BaseModel
{
    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_qty',
        'actual_qty',
        'difference',
    ];

    protected function casts(): array
    {
        return [
            'system_qty' => 'decimal:2',
            'actual_qty' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function opname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
