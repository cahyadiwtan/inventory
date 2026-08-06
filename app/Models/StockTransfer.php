<?php

namespace App\Models;

class StockTransfer extends BaseModel
{
    protected $fillable = [
        'number',
        'from_warehouse_id',
        'to_warehouse_id',
        'status',
        'notes',
        'created_by',
    ];

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_TRANSFERRED = 'transferred';
    public const STATUS_RECEIVED = 'received';

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movements()
    {
        return $this->morphMany(StockMovement::class, 'reference', 'reference_type', 'reference_id');
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }
}
