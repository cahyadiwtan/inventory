<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Tax;
use App\Services\ActivityLogService;
use App\Services\SalesService;
use Livewire\Component;

class QuotationEditComponent extends Component
{
    public Quotation $quotation;

    public ?string $customerId = null;

    public string $quotationDate = '';

    public string $validUntil = '';

    public string $notes = '';

    public array $items = [];

    public function mount(Quotation $quotation): void
    {
        abort_unless(in_array($quotation->status, [Quotation::STATUS_DRAFT, Quotation::STATUS_SENT]), 404);

        $this->quotation = $quotation->load('items');
        $this->customerId = $quotation->customer_id;
        $this->quotationDate = $quotation->quotation_date?->toDateString() ?? now()->toDateString();
        $this->validUntil = $quotation->valid_until?->toDateString() ?? now()->addDays(30)->toDateString();
        $this->notes = $quotation->notes ?? '';
        $this->items = $quotation->items
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'qty' => (float) $item->qty,
                'unit_price' => (float) $item->unit_price,
                'discount' => (float) $item->discount,
                'tax_id' => $item->tax_id,
            ])
            ->all();
    }

    public function cancel(): void
    {
        $this->redirect(route('sales.quotations.show', $this->quotation), navigate: true);
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

    public function save(): void
    {
        $this->validate();

        $this->quotation->update([
            'customer_id' => $this->customerId,
            'quotation_date' => $this->quotationDate,
            'valid_until' => $this->validUntil,
            'notes' => $this->notes,
        ]);

        $this->replaceItems();

        app(ActivityLogService::class)->log('update', $this->quotation, "updated quotation: {$this->quotation->number}");

        session()->flash('status', "Quotation {$this->quotation->number} diperbarui.");

        $this->redirect(route('sales.quotations.show', $this->quotation), navigate: true);
    }

    public function saveAndSend(): void
    {
        $this->validate();

        $this->quotation->update([
            'customer_id' => $this->customerId,
            'quotation_date' => $this->quotationDate,
            'valid_until' => $this->validUntil,
            'status' => Quotation::STATUS_SENT,
            'notes' => $this->notes,
        ]);

        $this->replaceItems();

        app(ActivityLogService::class)->log('update', $this->quotation, "updated quotation: {$this->quotation->number}");
        app(ActivityLogService::class)->log('send', $this->quotation, "sent quotation: {$this->quotation->number}");

        session()->flash('status', "Quotation {$this->quotation->number} diperbarui dan dikirim.");

        $this->redirect(route('sales.quotations.show', $this->quotation), navigate: true);
    }

    protected function replaceItems(): void
    {
        $sales = app(SalesService::class);

        $this->quotation->items()->forceDelete();

        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);
            $tax = $item['tax_id'] ? Tax::find($item['tax_id']) : null;

            $this->quotation->items()->create([
                'product_id' => $item['product_id'],
                'description' => $product?->name,
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'tax_id' => $item['tax_id'] ?? null,
                'line_total' => $sales->lineTotal($item['qty'], $item['unit_price'], $item['discount'] ?? 0, $tax),
            ]);
        }

        $this->quotation->refresh();

        $this->quotation->update([
            'subtotal' => $this->quotation->items->sum(fn ($i) => (float) $i->qty * (float) $i->unit_price),
            'discount_amount' => $this->quotation->items->sum(fn ($i) => $sales->lineDiscount($i->qty, $i->unit_price, $i->discount)),
            'tax_amount' => $this->quotation->items->sum(fn ($i) => $sales->lineTax($i->qty, $i->unit_price, $i->discount, $i->tax)),
            'total' => $this->quotation->items->sum(fn ($i) => (float) $i->line_total),
        ]);
    }

    public function render()
    {
        $sales = app(SalesService::class);
        $products = Product::query()->where('is_active', true)->orderBy('name')->get();

        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;
        $grandTotal = 0;

        foreach ($this->items as $item) {
            $tax = $item['tax_id'] ? Tax::find($item['tax_id']) : null;
            $subtotal += (float) $item['qty'] * (float) $item['unit_price'];
            $discountTotal += $sales->lineDiscount((float) $item['qty'], (float) $item['unit_price'], (float) ($item['discount'] ?? 0));
            $taxTotal += $sales->lineTax((float) $item['qty'], (float) $item['unit_price'], (float) ($item['discount'] ?? 0), $tax);
            $grandTotal += $sales->lineTotal((float) $item['qty'], (float) $item['unit_price'], (float) ($item['discount'] ?? 0), $tax);
        }

        return view('livewire.sales.quotation-create', [
            'customers' => $customers = Customer::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => $products,
            'taxes' => Tax::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedCustomer' => $this->customerId ? Customer::find($this->customerId) : null,
            'customerMap' => $customers->mapWithKeys(fn ($customer) => [
                $customer->id => [
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'pic_name' => $customer->pic_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
            ]),
            'stockLevels' => $this->stockLevels(),
            'subtotal' => $subtotal,
            'discountTotal' => $discountTotal,
            'taxTotal' => $taxTotal,
            'grandTotal' => $grandTotal,
            'sales' => $sales,
            'productPrices' => $products->pluck('selling_price', 'id'),
            'editing' => true,
            'editingQuotation' => $this->quotation,
        ])->title("Edit {$this->quotation->number} | Inventory System");
    }

    protected function stockLevels(): array
    {
        $ids = collect($this->items)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return [];
        }

        return Product::withSum('warehouses as total_stock', 'qty_on_hand')
            ->whereIn('id', $ids)
            ->get()
            ->mapWithKeys(fn ($product) => [
                $product->id => [
                    'name' => $product->name,
                    'stock' => (float) $product->total_stock,
                    'qty' => (float) collect($this->items)->firstWhere('product_id', $product->id)['qty'] ?? 0,
                ],
            ])
            ->all();
    }
}
