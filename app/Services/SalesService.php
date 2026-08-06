<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Tax;

class SalesService
{
    /**
     * Compute a line total with discount (percent) and per-line tax.
     *
     * total = qty*price - (qty*price*discount/100) + tax on the discounted base.
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
     * Create a Sales Order from an accepted quotation (draft status).
     */
    public function convertQuotationToOrder(Quotation $quotation, ?string $createdBy = null): SalesOrder
    {
        $order = SalesOrder::create([
            'number' => app(NumberingService::class)->next('sales_order'),
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_DRAFT,
            'notes' => $quotation->notes,
            'subtotal' => $quotation->subtotal,
            'discount_amount' => $quotation->discount_amount,
            'tax_amount' => $quotation->tax_amount,
            'total' => $quotation->total,
            'created_by' => $createdBy,
        ]);

        foreach ($quotation->items as $item) {
            $order->items()->create([
                'quotation_item_id' => $item->id,
                'product_id' => $item->product_id,
                'description' => $item->description,
                'qty' => $item->qty,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'tax_id' => $item->tax_id,
                'line_total' => $item->line_total,
            ]);
        }

        return $order;
    }

    /**
     * Quantity of an SO item already shipped via posted delivery orders.
     */
    public function deliveredQty(SalesOrderItem $item): float
    {
        return (float) DeliveryOrder::query()
            ->where('sales_order_id', $item->sales_order_id)
            ->where('status', DeliveryOrder::STATUS_POSTED)
            ->whereHas('items', fn ($q) => $q->where('sales_order_item_id', $item->id))
            ->join('delivery_order_items', 'delivery_orders.id', '=', 'delivery_order_items.delivery_order_id')
            ->where('delivery_order_items.sales_order_item_id', $item->id)
            ->sum('delivery_order_items.qty');
    }

    /**
     * Remaining deliverable qty for an SO item.
     */
    public function remainingQty(SalesOrderItem $item): float
    {
        return round((float) $item->qty - $this->deliveredQty($item), 2);
    }
}
