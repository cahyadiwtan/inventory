<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Tax;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\SalesService;
use Livewire\Component;

class QuotationComponent extends Component
{
    public ?string $customerId = null;

    public string $quotationDate = '';

    public string $validUntil = '';

    public string $notes = '';

    public array $items = [];

    public function mount(): void
    {
        $this->quotationDate = now()->toDateString();
        $this->validUntil = now()->addDays(30)->toDateString();
        $this->items = [];
    }

    public function render()
    {
        $quotations = Quotation::with(['customer', 'items.product', 'salesOrder'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.sales.quotation', [
            'quotations' => $quotations,
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'taxes' => Tax::query()->where('is_active', true)->orderBy('name')->get(),
            'salesService' => app(SalesService::class),
        ])->title('Quotation | Inventory System');
    }

    public function rules(): array
    {
        return [
            'customerId' => ['required', 'exists:customers,id'],
            'quotationDate' => ['required', 'date'],
            'validUntil' => ['required', 'date', 'after_or_equal:quotationDate'],
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
                $this->items[$index]['unit_price'] = (float) $product->selling_price;
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $quotation = Quotation::create([
            'number' => app(NumberingService::class)->next('quotation'),
            'customer_id' => $this->customerId,
            'quotation_date' => $this->quotationDate,
            'valid_until' => $this->validUntil,
            'status' => Quotation::STATUS_DRAFT,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        $this->storeItems($quotation);

        app(ActivityLogService::class)->log('create', $quotation, "created quotation: {$quotation->number}");

        session()->flash('status', "Quotation {$quotation->number} dibuat.");

        $this->reset(['customerId', 'notes']);
        $this->items = [];
    }

    public function send(string $id): void
    {
        $quotation = Quotation::findOrFail($id);

        if ($quotation->status !== Quotation::STATUS_DRAFT) {
            return;
        }

        $quotation->update(['status' => Quotation::STATUS_SENT]);
        app(ActivityLogService::class)->log('send', $quotation, "sent quotation: {$quotation->number}");
    }

    public function accept(string $id): void
    {
        $quotation = Quotation::findOrFail($id);

        if ($quotation->status !== Quotation::STATUS_SENT) {
            return;
        }

        $quotation->update(['status' => Quotation::STATUS_ACCEPTED]);
        app(ActivityLogService::class)->log('accept', $quotation, "accepted quotation: {$quotation->number}");
    }

    public function reject(string $id): void
    {
        $quotation = Quotation::findOrFail($id);

        if ($quotation->status !== Quotation::STATUS_SENT) {
            return;
        }

        $quotation->update(['status' => Quotation::STATUS_REJECTED]);
        app(ActivityLogService::class)->log('reject', $quotation, "rejected quotation: {$quotation->number}");
    }

    public function expire(string $id): void
    {
        $quotation = Quotation::findOrFail($id);

        if (! in_array($quotation->status, [Quotation::STATUS_DRAFT, Quotation::STATUS_SENT])) {
            return;
        }

        $quotation->update(['status' => Quotation::STATUS_EXPIRED]);
        app(ActivityLogService::class)->log('expire', $quotation, "expired quotation: {$quotation->number}");
    }

    public function convert(string $id): void
    {
        $quotation = Quotation::with('items')->findOrFail($id);

        if (! $quotation->isConvertible()) {
            return;
        }

        $order = app(SalesService::class)->convertQuotationToOrder($quotation, auth()->id());

        app(ActivityLogService::class)->log('convert', $quotation, "converted quotation to order: {$order->number}");

        session()->flash('status', "Quotation {$quotation->number} dikonversi menjadi {$order->number}.");
    }

    protected function storeItems(Quotation $quotation): void
    {
        $sales = app(SalesService::class);

        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);
            $tax = $item['tax_id'] ? Tax::find($item['tax_id']) : null;

            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'description' => $product?->name,
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'tax_id' => $item['tax_id'] ?? null,
                'line_total' => $sales->lineTotal($item['qty'], $item['unit_price'], $item['discount'] ?? 0, $tax),
            ]);
        }

        $quotation->update([
            'subtotal' => $quotation->items->sum(fn ($i) => (float) $i->qty * (float) $i->unit_price),
            'discount_amount' => $quotation->items->sum(fn ($i) => $sales->lineDiscount($i->qty, $i->unit_price, $i->discount)),
            'tax_amount' => $quotation->items->sum(fn ($i) => $sales->lineTax($i->qty, $i->unit_price, $i->discount, $i->tax)),
            'total' => $quotation->items->sum(fn ($i) => (float) $i->line_total),
        ]);
    }
}
