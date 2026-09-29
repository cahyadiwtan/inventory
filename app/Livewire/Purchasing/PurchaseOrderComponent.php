<?php

namespace App\Livewire\Purchasing;

use App\Models\Approval;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tax;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\PurchaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class PurchaseOrderComponent extends Component
{
    public ?string $supplierId = null;

    public string $orderDate = '';

    public string $expectedDate = '';

    public string $notes = '';

    public array $items = [];

    public ?string $selectedOrderId = null;

    public function mount(): void
    {
        $this->orderDate = now()->toDateString();
        $this->expectedDate = now()->addDays(7)->toDateString();
        $this->items = [];
    }

    public function render()
    {
        $orders = PurchaseOrder::with(['supplier', 'items.product', 'goodsReceipts'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.purchasing.purchase-order', [
            'orders' => $orders,
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'taxes' => Tax::query()->where('is_active', true)->orderBy('name')->get(),
            'purchaseService' => app(PurchaseService::class),
        ])->title('Purchase Order | Inventory System');
    }

    public function rules(): array
    {
        return [
            'supplierId' => ['required', 'exists:suppliers,id'],
            'orderDate' => ['required', 'date'],
            'expectedDate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => '', 'qty' => 1, 'unit_price' => 0, 'discount' => 0, 'tax_id' => null];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key): void
    {
        if (! is_string($key) || ! str_contains($key, '.')) {
            return;
        }

        [$index, $field] = explode('.', $key);

        if ($field === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = (float) $product->purchase_price;
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $order = PurchaseOrder::create([
            'number' => app(NumberingService::class)->next('purchase_order'),
            'supplier_id' => $this->supplierId,
            'order_date' => $this->orderDate,
            'expected_date' => $this->expectedDate ?: null,
            'status' => PurchaseOrder::STATUS_DRAFT,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        $this->storeItems($order);

        app(ActivityLogService::class)->log('create', $order, "created purchase order: {$order->number}");

        session()->flash('status', "Purchase Order {$order->number} dibuat.");

        $this->reset(['supplierId', 'notes']);
        $this->items = [];
    }

    public function print(string $id): void
    {
        $this->selectedOrderId = $id;
        $this->dispatch('print');
    }

    public function exportPdf(string $id): \Symfony\Component\HttpFoundation\Response
    {
        $order = PurchaseOrder::with(['supplier', 'items.product', 'items.tax', 'creator'])->findOrFail($id);

        $data = [
            'purchaseOrder' => $order,
            'company' => \App\Support\CompanyProfile::data(),
            'logo' => \App\Support\CompanyProfile::logoDataUri(),
        ];

        $pdf = Pdf::loadView('pdf.purchase-order', $data)->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'purchase-order-'.$order->number.'-'.now()->format('Ymd-His').'.pdf'
        );
    }

    public function getSelectedOrderForPrintingProperty(): ?PurchaseOrder
    {
        if (! $this->selectedOrderId) {
            return null;
        }

        return PurchaseOrder::with(['supplier', 'items.product', 'items.tax', 'creator'])
            ->find($this->selectedOrderId);
    }

    public function approve(string $id): void
    {
        $order = PurchaseOrder::findOrFail($id);

        if ($order->status !== PurchaseOrder::STATUS_DRAFT) {
            return;
        }

        $order->update(['status' => PurchaseOrder::STATUS_APPROVED]);
        $this->recordApproval($order, 'approve');

        app(ActivityLogService::class)->log('approve', $order, "approved purchase order: {$order->number}");
    }

    public function reject(string $id): void
    {
        $order = PurchaseOrder::findOrFail($id);

        if ($order->status !== PurchaseOrder::STATUS_DRAFT) {
            return;
        }

        $order->update(['status' => PurchaseOrder::STATUS_REJECTED]);
        $this->recordApproval($order, 'reject');

        app(ActivityLogService::class)->log('reject', $order, "rejected purchase order: {$order->number}");
    }

    public function cancel(string $id): void
    {
        $order = PurchaseOrder::findOrFail($id);

        if (! in_array($order->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_APPROVED])) {
            return;
        }

        $order->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
        $this->recordApproval($order, 'reject');

        app(ActivityLogService::class)->log('reject', $order, "cancelled purchase order: {$order->number}");
    }

    protected function storeItems(PurchaseOrder $order): void
    {
        $purchase = app(PurchaseService::class);

        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);
            $tax = $item['tax_id'] ? Tax::find($item['tax_id']) : null;

            $order->items()->create([
                'product_id' => $item['product_id'],
                'description' => $product?->name,
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'tax_id' => $item['tax_id'] ?? null,
                'line_total' => $purchase->lineTotal($item['qty'], $item['unit_price'], $item['discount'] ?? 0, $tax),
            ]);
        }

        $order->update([
            'subtotal' => $order->items->sum(fn ($i) => (float) $i->qty * (float) $i->unit_price),
            'discount_amount' => $order->items->sum(fn ($i) => $purchase->lineDiscount($i->qty, $i->unit_price, $i->discount)),
            'tax_amount' => $order->items->sum(fn ($i) => $purchase->lineTax($i->qty, $i->unit_price, $i->discount, $i->tax)),
            'total' => $order->items->sum(fn ($i) => (float) $i->line_total),
        ]);
    }

    protected function recordApproval(PurchaseOrder $order, string $action): void
    {
        Approval::create([
            'approvable_type' => PurchaseOrder::class,
            'approvable_id' => $order->id,
            'approver_id' => auth()->id(),
            'action' => $action,
        ]);
    }
}
