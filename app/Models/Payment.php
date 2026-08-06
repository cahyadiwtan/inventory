<?php

namespace App\Models;

class Payment extends BaseModel
{
    protected $fillable = [
        'number',
        'payable_type',
        'payable_id',
        'payment_date',
        'payment_method',
        'reference',
        'amount',
        'status',
        'notes',
        'created_by',
    ];

    public const STATUS_POSTED = 'posted';
    public const STATUS_VOID = 'void';

    public const METHODS = ['cash', 'bank_transfer', 'check', 'credit', 'other'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
