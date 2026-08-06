<?php

namespace App\Models;

class QuotationItem extends BaseModel
{
    protected $fillable = [
        'quotation_id',
        'product_id',
        'description',
        'qty',
        'unit_price',
        'discount',
        'tax_id',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}
