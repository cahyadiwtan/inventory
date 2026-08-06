<?php

namespace App\Models;

class StockTransferItem extends BaseModel
{
    protected $fillable = ['stock_transfer_id', 'product_id', 'qty'];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
        ];
    }

    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
