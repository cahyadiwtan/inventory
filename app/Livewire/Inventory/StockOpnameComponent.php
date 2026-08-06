<?php

namespace App\Livewire\Inventory;

use App\Models\Approval;
use App\Models\ProductWarehouse;
use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\StockService;
use Livewire\Component;

class StockOpnameComponent extends Component
{
    public ?string $warehouseId = null;

    public string $opnameDate = '';

    public string $notes = '';

    public array $items = [];

    public function render()
    {
        $opnames = StockOpname::with(['warehouse', 'items.product', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.inventory.stock-opname', [
            'opnames' => $opnames,
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'stockService' => app(StockService::class),
        ])->title('Stock Opname | Inventory System');
    }

    public function rules(): array
    {
        return [
            'warehouseId' => ['required', 'exists:warehouses,id'],
            'opnameDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.actual_qty' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Preload all products with stock in the selected warehouse.
     */
    public function loadProducts(): void
    {
        $this->validate([
            'warehouseId' => ['required', 'exists:warehouses,id'],
        ]);

        $this->items = ProductWarehouse::query()
            ->where('warehouse_id', $this->warehouseId)
            ->with('product')
            ->get()
            ->map(fn ($pw) => [
                'product_id' => $pw->product_id,
                'product_name' => $pw->product?->name,
                'system_qty' => (string) $pw->qty_on_hand,
                'actual_qty' => (string) $pw->qty_on_hand,
            ])
            ->all();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function submit(): void
    {
        $this->validate();

        $opname = StockOpname::create([
            'number' => app(NumberingService::class)->next('stock_opname'),
            'warehouse_id' => $this->warehouseId,
            'opname_date' => $this->opnameDate,
            'status' => StockOpname::STATUS_SUBMITTED,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $systemQty = (float) $item['system_qty'];
            $actualQty = (float) $item['actual_qty'];

            $opname->items()->create([
                'product_id' => $item['product_id'],
                'system_qty' => $systemQty,
                'actual_qty' => $actualQty,
                'difference' => $actualQty - $systemQty,
            ]);
        }

        $this->recordApproval($opname, 'submit');

        app(ActivityLogService::class)->log('submit', $opname, "submitted opname: {$opname->number}");

        session()->flash('status', "Opname {$opname->number} submitted.");

        $this->reset(['warehouseId', 'opnameDate', 'notes', 'items']);
    }

    public function approve(string $id): void
    {
        $opname = StockOpname::findOrFail($id);

        if ($opname->status !== StockOpname::STATUS_SUBMITTED) {
            return;
        }

        $opname->update(['status' => StockOpname::STATUS_APPROVED]);
        $this->recordApproval($opname, 'approve');

        app(ActivityLogService::class)->log('approve', $opname, "approved opname: {$opname->number}");
    }

    public function reject(string $id): void
    {
        $opname = StockOpname::findOrFail($id);

        if ($opname->status !== StockOpname::STATUS_SUBMITTED) {
            return;
        }

        $opname->update(['status' => StockOpname::STATUS_REJECTED]);
        $this->recordApproval($opname, 'reject');

        app(ActivityLogService::class)->log('reject', $opname, "rejected opname: {$opname->number}");
    }

    /**
     * Post approved opname: applies the difference as stock movement per item.
     */
    public function post(string $id): void
    {
        $opname = StockOpname::with('items')->findOrFail($id);

        if ($opname->status !== StockOpname::STATUS_APPROVED) {
            return;
        }

        $stock = app(StockService::class);
        $movements = [];

        foreach ($opname->items as $item) {
            $difference = (float) $item->difference;

            if (abs($difference) < 0.005) {
                continue;
            }

            $movementType = $difference > 0 ? 'in' : 'out';
            $reason = "Opname {$opname->number} selisih " . ($difference > 0 ? '+' : '') . number_format($difference, 2);

            $stock->move(
                $item->product_id,
                $opname->warehouse_id,
                $movementType,
                abs($difference),
                $opname,
                $reason,
                auth()->id(),
            );

            $movements[] = "{$item->product_id}:{$movementType}:{$difference}";
        }

        $opname->update(['status' => StockOpname::STATUS_POSTED]);
        $this->recordApproval($opname, 'post');

        app(ActivityLogService::class)->log('post', $opname, "posted opname: {$opname->number}");

        session()->flash('status', "Opname {$opname->number} posted.");
    }

    public function delete(string $id): void
    {
        $opname = StockOpname::findOrFail($id);

        if (! in_array($opname->status, [StockOpname::STATUS_DRAFT, StockOpname::STATUS_REJECTED])) {
            session()->flash('error', 'Opname yang sudah diproses tidak dapat dihapus.');

            return;
        }

        app(ActivityLogService::class)->log('delete', $opname, "deleted opname: {$opname->number}");
        $opname->delete();

        session()->flash('status', 'Opname deleted.');
    }

    protected function recordApproval(StockOpname $opname, string $action): void
    {
        Approval::create([
            'approvable_type' => StockOpname::class,
            'approvable_id' => $opname->id,
            'approver_id' => auth()->id(),
            'action' => $action,
            'notes' => $this->notes,
        ]);
    }
}
