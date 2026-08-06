<?php

namespace App\Services;

use App\Models\ProductWarehouse;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Mutate stock for a product in a warehouse.
     *
     * This is the single source of truth for stock changes: it updates the
     * product_warehouses.qty_on_hand and records a stock_movements ledger row
     * inside one transaction.
     *
     * @param  string  $type  'in' or 'out'
     * @throws \RuntimeException when qty is invalid or stock insufficient.
     */
    public function move(
        string $productId,
        string $warehouseId,
        string $type,
        float $qty,
        ?Model $reference = null,
        ?string $reason = null,
        ?string $createdBy = null,
    ): StockMovement {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Qty harus lebih dari 0.');
        }

        if (! in_array($type, ['in', 'out'])) {
            throw new \InvalidArgumentException('Movement type harus "in" atau "out".');
        }

        return DB::transaction(function () use ($productId, $warehouseId, $type, $qty, $reference, $reason, $createdBy) {
            $stock = ProductWarehouse::firstOrCreate([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ], ['qty_on_hand' => 0]);

            $qtyBefore = (float) $stock->qty_on_hand;
            $qtyAfter = $type === 'in' ? $qtyBefore + $qty : $qtyBefore - $qty;

            if ($qtyAfter < 0) {
                throw new \RuntimeException('Stock tidak mencukupi.');
            }

            $stock->update(['qty_on_hand' => $qtyAfter]);

            return StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'movement_type' => $type,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->getKey() : null,
                'qty' => $qty,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'reason' => $reason,
                'created_by' => $createdBy,
            ]);
        });
    }

    /**
     * Stock on hand for a product in a warehouse.
     */
    public function quantity(string $productId, string $warehouseId): float
    {
        return (float) ProductWarehouse::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('qty_on_hand') ?? 0;
    }

    /**
     * Transfer stock between warehouses:
     * OUT from source, IN to destination (kept atomic).
     */
    public function transfer(
        string $productId,
        string $fromWarehouseId,
        string $toWarehouseId,
        float $qty,
        ?Model $reference = null,
        ?string $reason = null,
        ?string $createdBy = null,
    ): void {
        DB::transaction(function () use ($productId, $fromWarehouseId, $toWarehouseId, $qty, $reference, $reason, $createdBy) {
            $this->move($productId, $fromWarehouseId, 'out', $qty, $reference, $reason, $createdBy);
            $this->move($productId, $toWarehouseId, 'in', $qty, $reference, $reason, $createdBy);
        });
    }
}
