<?php

namespace App\Livewire\Purchasing;

use App\Models\Approval;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\PurchaseService;
use App\Services\StockService;
use Livewire\Component;

class GoodsReceiptComponent extends Component
{
    public ?string $purchaseOrderId = null;

    public ?string $warehouseId = null;

    public string $receiptDate = '';

    public string $notes = '';

    public array $items = [];

    public function mount(): void
    {
        $this->receiptDate = now()->toDateString();
    }

    public function render()
    {
        $receipts = GoodsReceipt::with(['purchaseOrder.supplier', 'warehouse', 'items.product', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.purchasing.goods-receipt', [
            'receipts' => $receipts,
            'openOrders' => PurchaseOrder::with(['supplier', 'items'])
                ->where('status', PurchaseOrder::STATUS_APPROVED)
                ->orderByDesc('created_at')
                ->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'purchaseService' => app(PurchaseService::class),
        ])->title('Goods Receipt | Inventory System');
    }

    public function updatedPurchaseOrderId($value): void
    {
        $this->items = [];

        if (! $value) {
            return;
        }

        $order = PurchaseOrder::with('items')->find($value);

        if (! $order) {
            return;
        }

        $purchase = app(PurchaseService::class);

        foreach ($order->items as $item) {
            $remaining = $purchase->remainingQty($item);

            if ($remaining <= 0) {
                continue;
            }

            $this->items[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'order_qty' => (float) $item->qty,
                'received' => round((float) $item->qty - $remaining, 2),
                'qty' => $remaining,
                'purchase_order_item_id' => $item->id,
            ];
        }
    }

    public function create(): void
    {
        $this->validate([
            'purchaseOrderId' => ['required', 'exists:purchase_orders,id'],
            'warehouseId' => ['required', 'exists:warehouses,id'],
            'receiptDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
        ]);

        $receipt = GoodsReceipt::create([
            'number' => app(NumberingService::class)->next('goods_receipt'),
            'purchase_order_id' => $this->purchaseOrderId,
            'warehouse_id' => $this->warehouseId,
            'receipt_date' => $this->receiptDate,
            'status' => GoodsReceipt::STATUS_DRAFT,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $receipt->items()->create([
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
            ]);
        }

        $this->recordApproval($receipt, 'submit');

        app(ActivityLogService::class)->log('submit', $receipt, "created goods receipt: {$receipt->number}");

        session()->flash('status', "Goods Receipt {$receipt->number} dibuat.");

        $this->reset(['purchaseOrderId', 'warehouseId', 'notes']);
        $this->items = [];
    }

    public function post(string $id): void
    {
        $receipt = GoodsReceipt::with(['items', 'purchaseOrder.items'])->findOrFail($id);

        if ($receipt->status !== GoodsReceipt::STATUS_DRAFT) {
            return;
        }

        $purchase = app(PurchaseService::class);
        $stock = app(StockService::class);

        foreach ($receipt->items as $item) {
            $poItem = $receipt->purchaseOrder->items->firstWhere('id', $item->purchase_order_item_id);

            if ($poItem && $item->qty > $purchase->remainingQty($poItem)) {
                throw new \RuntimeException('Qty melebihi sisa pesanan.');
            }

            $stock->move(
                $item->product_id,
                $receipt->warehouse_id,
                'in',
                (float) $item->qty,
                $receipt,
                "Goods receipt {$receipt->number}",
                auth()->id(),
            );
        }

        $receipt->update(['status' => GoodsReceipt::STATUS_POSTED]);
        $this->recordApproval($receipt, 'post');

        $purchaseOrder = $receipt->purchaseOrder;

        if ($purchaseOrder->items->every(fn ($i) => $purchase->remainingQty($i) <= 0)) {
            $purchaseOrder->update(['status' => PurchaseOrder::STATUS_RECEIVED]);
        }

        app(ActivityLogService::class)->log('post', $receipt, "posted goods receipt: {$receipt->number}");

        session()->flash('status', "Goods Receipt {$receipt->number} diposting, stok masuk.");
    }

    protected function recordApproval(GoodsReceipt $receipt, string $action): void
    {
        Approval::create([
            'approvable_type' => GoodsReceipt::class,
            'approvable_id' => $receipt->id,
            'approver_id' => auth()->id(),
            'action' => $action,
        ]);
    }
}
