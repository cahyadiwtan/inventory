<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrderItem;
use App\Models\Tax;

class PurchaseService
{
    /**
     * Compute a line total with discount (percent) and per-line tax.
     */
    public function lineTotal(float $qty, float $unitPrice, float $discount = 0, ?Tax $tax = null): float
    {
        $subtotal = $qty * $unitPrice;
        $discountAmount = $subtotal * $discount / 100;
        $base = $subtotal - $discountAmount;
        $taxAmount = $tax ? $base * $tax->rate / 100 : 0;

        return round($base + $taxAmount, 2);
    }

    public function lineDiscount(float $qty, float $unitPrice, float $discount = 0): float
    {
        return round($qty * $unitPrice * $discount / 100, 2);
    }

    public function lineTax(float $qty, float $unitPrice, float $discount = 0, ?Tax $tax = null): float
    {
        $base = $qty * $unitPrice * (1 - $discount / 100);

        return round($tax ? $base * $tax->rate / 100 : 0, 2);
    }

    /**
     * Quantity of a PO item already received via posted goods receipts.
     */
    public function receivedQty(PurchaseOrderItem $item): float
    {
        return (float) GoodsReceipt::query()
            ->where('purchase_order_id', $item->purchase_order_id)
            ->where('status', GoodsReceipt::STATUS_POSTED)
            ->join('goods_receipt_items', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
            ->where('goods_receipt_items.purchase_order_item_id', $item->id)
            ->sum('goods_receipt_items.qty');
    }

    /**
     * Remaining receivable qty for a PO item.
     */
    public function remainingQty(PurchaseOrderItem $item): float
    {
        return round((float) $item->qty - $this->receivedQty($item), 2);
    }
}
