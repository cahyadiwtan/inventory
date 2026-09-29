<?php

namespace App\Livewire\Sales;

use App\Models\Approval;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\Tax;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\SalesService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DirectInvoiceComponent extends Component
{
    public ?string $customerId = null;

    public string $invoiceDate = '';

    public string $dueDate = '';

    public string $deliveryDate = '';

    public ?string $warehouseId = null;

    public string $notes = '';

    public array $items = [];

    public function mount(): void
    {
        $this->invoiceDate = now()->toDateString();
        $this->dueDate = now()->addDays(14)->toDateString();
        $this->deliveryDate = now()->toDateString();
        $this->items = [];
    }

    public function updatedCustomerId($value): void
    {
        if ($value) {
            $customer = Customer::find($value);

            if ($customer && $customer->payment_term_days) {
                $this->dueDate = now()->addDays($customer->payment_term_days)->toDateString();
            }
        }
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
            'invoiceDate' => ['required', 'date'],
            'dueDate' => ['required', 'date', 'after_or_equal:invoiceDate'],
            'deliveryDate' => ['required', 'date'],
            'warehouseId' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function create(): void
    {
        $this->validate();

        DB::transaction(function () {
            $numbering = app(NumberingService::class);
            $sales = app(SalesService::class);
            $stock = app(StockService::class);

            $invoice = SalesInvoice::create([
                'number' => $numbering->next('direct_invoice'),
                'sales_order_id' => null,
                'customer_id' => $this->customerId,
                'invoice_date' => $this->invoiceDate,
                'due_date' => $this->dueDate,
                'status' => SalesInvoice::STATUS_POSTED,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                $product = Product::find($item['product_id']);
                $tax = $item['tax_id'] ? Tax::find($item['tax_id']) : null;

                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'description' => $product?->name,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_id' => $item['tax_id'] ?? null,
                    'line_total' => $sales->lineTotal($item['qty'], $item['unit_price'], $item['discount'] ?? 0, $tax),
                ]);
            }

            $invoice->update([
                'subtotal' => $invoice->items->sum(fn ($i) => (float) $i->qty * (float) $i->unit_price),
                'discount_amount' => $invoice->items->sum(fn ($i) => $sales->lineDiscount($i->qty, $i->unit_price, $i->discount)),
                'tax_amount' => $invoice->items->sum(fn ($i) => $sales->lineTax($i->qty, $i->unit_price, $i->discount, $i->tax)),
                'total' => $invoice->items->sum(fn ($i) => (float) $i->line_total),
            ]);

            $delivery = DeliveryOrder::create([
                'number' => $numbering->next('delivery_order'),
                'sales_order_id' => null,
                'sales_invoice_id' => $invoice->id,
                'warehouse_id' => $this->warehouseId,
                'delivery_date' => $this->deliveryDate,
                'status' => DeliveryOrder::STATUS_POSTED,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                $delivery->items()->create([
                    'sales_order_item_id' => null,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }

            foreach ($delivery->items as $item) {
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

            $this->recordApproval($invoice, 'submit');
            $this->recordApproval($invoice, 'post');
            $this->recordApproval($delivery, 'submit');
            $this->recordApproval($delivery, 'post');

            app(ActivityLogService::class)->log('submit', $invoice, "created direct invoice: {$invoice->number}");
            app(ActivityLogService::class)->log('post', $invoice, "posted direct invoice: {$invoice->number}");
            app(ActivityLogService::class)->log('submit', $delivery, "created delivery: {$delivery->number}");
            app(ActivityLogService::class)->log('post', $delivery, "posted delivery: {$delivery->number}");

            $this->redirect(route('sales.direct-invoices.show', $invoice), navigate: true);
        });
    }

    protected function recordApproval($document, string $action): void
    {
        Approval::create([
            'approvable_type' => get_class($document),
            'approvable_id' => $document->id,
            'approver_id' => auth()->id(),
            'action' => $action,
        ]);
    }

    public function render()
    {
        $sales = app(SalesService::class);
        $products = Product::query()->where('is_active', true)->orderBy('name')->get();
        $customers = Customer::query()->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::query()->where('is_active', true)->orderBy('name')->get();

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

        $invoices = SalesInvoice::with(['customer', 'items.product', 'deliveryOrder'])
            ->whereNull('sales_order_id')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.sales.direct-invoice', [
            'customers' => $customers,
            'products' => $products,
            'taxes' => $taxes,
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'invoices' => $invoices,
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
        ])->title('Direct Invoice | Inventory System');
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