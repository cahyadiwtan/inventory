<?php

namespace App\Models;

class Customer extends BaseModel
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

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }
}
