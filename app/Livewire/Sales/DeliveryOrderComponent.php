<?php

namespace App\Livewire\Sales;

use App\Models\Approval;
use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\SalesService;
use App\Services\StockService;
use Livewire\Component;

class DeliveryOrderComponent extends Component
{
    public ?string $salesOrderId = null;

    public ?string $warehouseId = null;

    public string $deliveryDate = '';

    public string $notes = '';

    public array $items = [];

    public function mount(): void
    {
        $this->deliveryDate = now()->toDateString();
    }

    public function render()
    {
        $deliveries = DeliveryOrder::with(['salesOrder.customer', 'warehouse', 'items.product', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.sales.delivery-order', [
            'deliveries' => $deliveries,
            'openOrders' => SalesOrder::with(['customer', 'items'])
                ->where('status', SalesOrder::STATUS_APPROVED)
                ->orderByDesc('created_at')
                ->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'salesService' => app(SalesService::class),
        ])->title('Delivery Order | Inventory System');
    }

    public function updatedSalesOrderId($value): void
    {
        $this->items = [];

        if (! $value) {
            return;
        }

        $order = SalesOrder::with('items')->find($value);

        if (! $order) {
            return;
        }

        $sales = app(SalesService::class);

        foreach ($order->items as $item) {
            $remaining = $sales->remainingQty($item);

            if ($remaining <= 0) {
                continue;
            }

            $this->items[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'order_qty' => (float) $item->qty,
                'delivered' => round((float) $item->qty - $remaining, 2),
                'qty' => $remaining,
                'sales_order_item_id' => $item->id,
            ];
        }
    }

    public function create(): void
    {
        $this->validate([
            'salesOrderId' => ['required', 'exists:sales_orders,id'],
            'warehouseId' => ['required', 'exists:warehouses,id'],
            'deliveryDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
        ]);

        $delivery = DeliveryOrder::create([
            'number' => app(NumberingService::class)->next('delivery_order'),
            'sales_order_id' => $this->salesOrderId,
            'warehouse_id' => $this->warehouseId,
            'delivery_date' => $this->deliveryDate,
            'status' => DeliveryOrder::STATUS_DRAFT,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $delivery->items()->create([
                'sales_order_item_id' => $item['sales_order_item_id'],
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
            ]);
        }

        $this->recordApproval($delivery, 'submit');

        app(ActivityLogService::class)->log('submit', $delivery, "created delivery: {$delivery->number}");

        session()->flash('status', "Delivery Order {$delivery->number} dibuat.");

        $this->reset(['salesOrderId', 'warehouseId', 'notes']);
        $this->items = [];
    }

    public function post(string $id): void
    {
        $delivery = DeliveryOrder::with(['items', 'salesOrder.items'])->findOrFail($id);

        if ($delivery->status !== DeliveryOrder::STATUS_DRAFT) {
            return;
        }

        $sales = app(SalesService::class);
        $stock = app(StockService::class);

        foreach ($delivery->items as $item) {
            $soItem = $delivery->salesOrder->items->firstWhere('id', $item->sales_order_item_id);

            if ($soItem && $item->qty > $sales->remainingQty($soItem)) {
                throw new \RuntimeException('Qty melebihi sisa pesanan.');
            }

            $stock->move(
                $item->product_id,
                $delivery->warehouse_id,
                'out',
                (float) $item->qty,
                $delivery,
                "Delivery {$delivery->number}",
                auth()->id(),
            );
        }

        $delivery->update(['status' => DeliveryOrder::STATUS_POSTED]);
        $this->recordApproval($delivery, 'post');

        app(ActivityLogService::class)->log('post', $delivery, "posted delivery: {$delivery->number}");

        session()->flash('status', "Delivery Order {$delivery->number} diposting, stok keluar.");
    }

    protected function recordApproval(DeliveryOrder $delivery, string $action): void
    {
        Approval::create([
            'approvable_type' => DeliveryOrder::class,
            'approvable_id' => $delivery->id,
            'approver_id' => auth()->id(),
            'action' => $action,
        ]);
    }
}
