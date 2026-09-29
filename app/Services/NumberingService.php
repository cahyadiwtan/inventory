<?php

namespace App\Services;

use App\Models\DocumentNumber;
use Illuminate\Support\Facades\DB;

class NumberingService
{
    public const PREFIXES = [
        'quotation' => 'QT',
        'sales_order' => 'SO',
        'delivery_order' => 'DO',
        'sales_invoice' => 'INV',
        'direct_invoice' => 'DINV',
        'purchase_order' => 'PO',
        'goods_receipt' => 'GRN',
        'purchase_invoice' => 'PINV',
        'payment' => 'PAY',
        'stock_adjustment' => 'ADJ',
        'stock_transfer' => 'TRF',
        'stock_opname' => 'OPN',
        'purchase_request' => 'PR',
    ];

    /**
     * Generate next document number with an atomic, locked counter.
     *
     * Format: PREFIX-YYYYMM-NNNNNN
     */
    public function next(string $key, ?\DateTimeInterface $date = null): string
    {
        $prefix = $this->prefixFor($key);
        $date ??= now();
        $period = $date->format('Ym');

        return DB::transaction(function () use ($prefix, $period) {
            $counter = DocumentNumber::query()
                ->lockForUpdate()
                ->firstOrCreate(
                    ['prefix' => $prefix, 'period' => $period],
                    ['last_number' => 0],
                );

            $counter->increment('last_number');

            return sprintf('%s-%s-%06d', $prefix, $period, $counter->fresh()->last_number);
        });
    }

    public function prefixFor(string $key): string
    {
        return self::PREFIXES[$key] ?? strtoupper($key);
    }
}
