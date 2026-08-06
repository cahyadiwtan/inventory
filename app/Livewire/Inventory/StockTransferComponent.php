<?php

namespace App\Livewire\Inventory;

use App\Models\Approval;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\StockService;
use Livewire\Component;

class StockTransferComponent extends Component
{
    public ?string $fromWarehouseId = null;

    public ?string $toWarehouseId = null;

    public string $notes = '';

    public array $items = [];

    public function render()
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.inventory.stock-transfer', [
            'transfers' => $transfers,
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'stockService' => app(StockService::class),
        ])->title('Stock Transfer | Inventory System');
    }

    public function rules(): array
    {
        return [
            'fromWarehouseId' => ['required', 'exists:warehouses,id', 'different:toWarehouseId'],
            'toWarehouseId' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => '', 'qty' => 0];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function request(): void
    {
        $this->validate();

        $transfer = StockTransfer::create([
            'number' => app(NumberingService::class)->next('stock_transfer'),
            'from_warehouse_id' => $this->fromWarehouseId,
            'to_warehouse_id' => $this->toWarehouseId,
            'status' => StockTransfer::STATUS_REQUESTED,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $transfer->items()->create([
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
            ]);
        }

        $this->recordApproval($transfer, 'submit', $this->notes);

        app(ActivityLogService::class)->log('submit', $transfer, "submitted transfer: {$transfer->number}");

        session()->flash('status', "Transfer {$transfer->number} requested.");

        $this->reset(['fromWarehouseId', 'toWarehouseId', 'notes', 'items']);
    }

    public function approve(string $id): void
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== StockTransfer::STATUS_REQUESTED) {
            return;
        }

        $transfer->update(['status' => StockTransfer::STATUS_APPROVED]);
        $this->recordApproval($transfer, 'approve');

        app(ActivityLogService::class)->log('approve', $transfer, "approved transfer: {$transfer->number}");
    }

    public function reject(string $id): void
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== StockTransfer::STATUS_REQUESTED) {
            return;
        }

        $transfer->update(['status' => StockTransfer::STATUS_REJECTED]);
        $this->recordApproval($transfer, 'reject');

        app(ActivityLogService::class)->log('reject', $transfer, "rejected transfer: {$transfer->number}");
    }

    public function transfer(string $id): void
    {
        $transfer = StockTransfer::with('items')->findOrFail($id);

        if ($transfer->status !== StockTransfer::STATUS_APPROVED) {
            return;
        }

        $stock = app(StockService::class);

        foreach ($transfer->items as $item) {
            $stock->move(
                $item->product_id,
                $transfer->from_warehouse_id,
                'out',
                (float) $item->qty,
                $transfer,
                "Transfer {$transfer->number}",
                auth()->id(),
            );
        }

        $transfer->update(['status' => StockTransfer::STATUS_TRANSFERRED]);
        $this->recordApproval($transfer, 'post');

        app(ActivityLogService::class)->log('post', $transfer, "transferred stock out: {$transfer->number}");
    }

    public function receive(string $id): void
    {
        $transfer = StockTransfer::with('items')->findOrFail($id);

        if ($transfer->status !== StockTransfer::STATUS_TRANSFERRED) {
            return;
        }

        $stock = app(StockService::class);

        foreach ($transfer->items as $item) {
            $stock->move(
                $item->product_id,
                $transfer->to_warehouse_id,
                'in',
                (float) $item->qty,
                $transfer,
                "Transfer {$transfer->number}",
                auth()->id(),
            );
        }

        $transfer->update(['status' => StockTransfer::STATUS_RECEIVED]);
        $this->recordApproval($transfer, 'post');

        app(ActivityLogService::class)->log('post', $transfer, "received transfer in: {$transfer->number}");

        session()->flash('status', "Transfer {$transfer->number} received.");
    }

    protected function recordApproval(StockTransfer $transfer, string $action, ?string $notes = null): void
    {
        Approval::create([
            'approvable_type' => StockTransfer::class,
            'approvable_id' => $transfer->id,
            'approver_id' => auth()->id(),
            'action' => $action,
            'notes' => $notes,
        ]);
    }
}
