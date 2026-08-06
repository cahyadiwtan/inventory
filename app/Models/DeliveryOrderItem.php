<?php

namespace App\Models;

class DeliveryOrderItem extends BaseModel
{
    protected $fillable = [
        'delivery_order_id',
        'sales_order_item_id',
        'product_id',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
        ];
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
