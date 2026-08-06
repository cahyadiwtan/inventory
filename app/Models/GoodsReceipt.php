<?php

namespace App\Models;

class GoodsReceipt extends BaseModel
{
    protected $fillable = [
        'number',
        'purchase_order_id',
        'warehouse_id',
        'receipt_date',
        'status',
        'notes',
        'created_by',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movements()
    {
        return $this->morphMany(StockMovement::class, 'reference', 'reference_type', 'reference_id');
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }
}
