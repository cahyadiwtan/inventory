<?php

namespace App\Models;

class DeliveryOrder extends BaseModel
{
    protected $fillable = [
        'number',
        'sales_order_id',
        'warehouse_id',
        'delivery_date',
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
            'delivery_date' => 'date',
        ];
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class);
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
