<?php

namespace App\Models;

class PurchaseInvoice extends BaseModel
{
    protected $fillable = [
        'number',
        'purchase_order_id',
        'supplier_id',
        'invoice_date',
        'due_date',
        'status',
        'notes',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
        'created_by',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_VOID = 'void';

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable', 'payable_type', 'payable_id');
    }

    public function balance(): float
    {
        return round((float) $this->total - (float) $this->paid_amount, 2);
    }

    public function isPosted(): bool
    {
        return in_array($this->status, [self::STATUS_POSTED, self::STATUS_PARTIAL, self::STATUS_PAID]);
    }
}
