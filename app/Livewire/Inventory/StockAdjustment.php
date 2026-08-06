<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\StockAdjustment as StockAdjustmentModel;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\StockService;
use Livewire\Component;

class StockAdjustment extends Component
{
    public string $type = 'plus';

    public string $reason = '';

    public ?string $warehouseId = null;

    public array $items = [];

    public function render()
    {
        $adjustments = StockAdjustmentModel::with(['warehouse', 'items.product', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.inventory.stock-adjustment', [
            'adjustments' => $adjustments,
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'stockService' => app(StockService::class),
        ])->title('Stock Adjustment | Inventory System');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:plus,minus'],
            'warehouseId' => ['required', 'exists:warehouses,id'],
            'reason' => ['required', 'string', 'max:255'],
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

    public function post(): void
    {
        $this->validate();

        $adjustment = StockAdjustmentModel::create([
            'number' => app(NumberingService::class)->next('stock_adjustment'),
            'warehouse_id' => $this->warehouseId,
            'type' => $this->type,
            'reason' => $this->reason,
            'status' => StockAdjustmentModel::STATUS_POSTED,
            'created_by' => auth()->id(),
        ]);

        $stock = app(StockService::class);
        $movementType = $this->type === 'plus' ? 'in' : 'out';

        foreach ($this->items as $item) {
            $adjustment->items()->create([
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
            ]);

            $stock->move(
                $item['product_id'],
                $this->warehouseId,
                $movementType,
                (float) $item['qty'],
                $adjustment,
                $this->reason,
                auth()->id(),
            );
        }

        app(ActivityLogService::class)->log('post', $adjustment, "posted {$adjustment->type} adjustment: {$adjustment->number}");

        session()->flash('status', "Adjustment {$adjustment->number} posted.");

        $this->reset(['type', 'reason', 'warehouseId', 'items']);
    }

    public function delete(string $id): void
    {
        $adjustment = StockAdjustmentModel::findOrFail($id);

        if ($adjustment->isPosted()) {
            session()->flash('error', 'Posted adjustment cannot be deleted.');

            return;
        }

        app(ActivityLogService::class)->log('delete', $adjustment, "deleted adjustment: {$adjustment->number}");
        $adjustment->delete();

        session()->flash('status', 'Adjustment deleted.');
    }
}
