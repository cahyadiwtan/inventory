<?php

namespace App\Models;

class Supplier extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'npwp',
        'address',
        'phone',
        'email',
        'pic_name',
        'payment_term_days',
        'credit_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payment_term_days' => 'integer',
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
