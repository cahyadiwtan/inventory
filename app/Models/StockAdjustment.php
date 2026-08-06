<?php

namespace App\Models;

class StockAdjustment extends BaseModel
{
    protected $fillable = [
        'number',
        'warehouse_id',
        'type',
        'reason',
        'status',
        'created_by',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
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
