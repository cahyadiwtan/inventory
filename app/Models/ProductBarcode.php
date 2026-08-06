<?php

namespace App\Models;

class ProductBarcode extends BaseModel
{
    protected $fillable = ['product_id', 'barcode'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
