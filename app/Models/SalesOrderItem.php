<?php

namespace App\Models;

class SalesOrderItem extends BaseModel
{
    protected $fillable = [
        'sales_order_id',
        'quotation_item_id',
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

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function quotationItem()
    {
        return $this->belongsTo(QuotationItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }
}
