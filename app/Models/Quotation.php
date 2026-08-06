<?php

namespace App\Models;

class Quotation extends BaseModel
{
    protected $fillable = [
        'number',
        'customer_id',
        'quotation_date',
        'valid_until',
        'status',
        'notes',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'created_by',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesOrder()
    {
        return $this->hasOne(SalesOrder::class);
    }

    public function isConvertible(): bool
    {
        return $this->status === self::STATUS_ACCEPTED && ! $this->salesOrder()->exists();
    }
}
