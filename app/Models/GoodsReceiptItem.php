<?php

namespace App\Models;

class GoodsReceiptItem extends BaseModel
{
    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
        ];
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
