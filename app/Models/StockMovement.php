<?php

namespace App\Models;

use App\Services\NumberingService;

class StockMovement extends BaseModel
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'qty',
        'qty_before',
        'qty_after',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'qty_before' => 'decimal:2',
            'qty_after' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}
