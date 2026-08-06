<?php

namespace App\Models;

class ProductPrice extends BaseModel
{
    protected $fillable = ['product_id', 'price_type', 'price', 'valid_from', 'valid_to'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
