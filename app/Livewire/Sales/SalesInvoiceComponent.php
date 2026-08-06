<?php

namespace App\Livewire\Sales;

use App\Models\Approval;
use App\Models\DeliveryOrder;
use App\Models\Payment;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\SalesService;
use Livewire\Component;

class SalesInvoiceComponent extends Component
{
    public ?string $salesOrderId = null;

    public string $invoiceDate = '';

    public string $dueDate = '';

    public string $notes = '';

    public array $items = [];

    public ?string $payingInvoiceId = null;

    public string $paymentDate = '';

    public string $paymentMethod = 'cash';

    public string $paymentAmount = '';

    public string $paymentReference = '';

    public function mount(): void
    {
        $this->invoiceDate = now()->toDateString();
        $this->dueDate = now()->addDays(14)->toDateString();
        $this->paymentDate = now()->toDateString();
    }

    public function render()
    {
        $invoices = SalesInvoice::with(['salesOrder.customer', 'customer', 'items.product', 'payments'])
            ->orderByDesc('created_at')
            ->get();

        $invoicableOrders = SalesOrder::with(['customer', 'items'])
            ->where('status', SalesOrder::STATUS_APPROVED)
            ->whereDoesntHave('invoice')
            ->get()
            ->filter(fn (SalesOrder $order) => $order->deliveryOrders()
                ->where('status', DeliveryOrder::STATUS_POSTED)->exists());

        return view('livewire.sales.sales-invoice', [
            'invoices' => $invoices,
            'invoicableOrders' => $invoicableOrders,
            'salesService' => app(SalesService::class),
        ])->title('Sales Invoice | Inventory System');
    }

    public function updatedSalesOrderId($value): void
    {
        $this->items = [];

        if (! $value) {
            return;
        }

        $order = SalesOrder::with('items.product')->find($value);

        if (! $order) {
            return;
        }

        $sales = app(SalesService::class);

        foreach ($order->items as $item) {
            $delivered = $sales->deliveredQty($item);

            if ($delivered <= 0) {
                continue;
            }

            $this->items[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'qty' => $delivered,
                'unit_price' => (float) $item->unit_price,
                'discount' => (float) $item->discount,
                'tax_id' => $item->tax_id,
            ];
        }

        if ($order->customer && $order->customer->payment_term_days) {
            $this->dueDate = now()->addDays($order->customer->payment_term_days)->toDateString();
        }
    }

    public function create(): void
    {
        $this->validate([
            'salesOrderId' => ['required', 'exists:sales_orders,id'],
            'invoiceDate' => ['required', 'date'],
            'dueDate' => ['required', 'date', 'after_or_equal:invoiceDate'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
        ]);

        $order = SalesOrder::findOrFail($this->salesOrderId);

        $invoice = SalesInvoice::create([
            'number' => app(NumberingService::class)->next('sales_invoice'),
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'invoice_date' => $this->invoiceDate,
            'due_date' => $this->dueDate,
            'status' => SalesInvoice::STATUS_DRAFT,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        $this->storeItems($invoice);

        $this->recordApproval($invoice, 'submit');

        app(ActivityLogService::class)->log('submit', $invoice, "created invoice: {$invoice->number}");

        session()->flash('status', "Invoice {$invoice->number} dibuat.");

        $this->reset(['salesOrderId', 'notes']);
        $this->items = [];
    }

    public function post(string $id): void
    {
        $invoice = SalesInvoice::findOrFail($id);

        if ($invoice->status !== SalesInvoice::STATUS_DRAFT) {
            return;
        }

        $invoice->update(['status' => SalesInvoice::STATUS_POSTED]);
        $this->recordApproval($invoice, 'post');

        app(ActivityLogService::class)->log('post', $invoice, "posted invoice: {$invoice->number}");
    }

    public function openPayment(string $id): void
    {
        $this->payingInvoiceId = $id;
        $invoice = SalesInvoice::findOrFail($id);
        $this->paymentAmount = (string) $invoice->balance();
    }

    public function closePayment(): void
    {
        $this->reset(['payingInvoiceId', 'paymentAmount', 'paymentReference']);
    }

    public function submitPayment(): void
    {
        $this->validate([
            'payingInvoiceId' => ['required', 'exists:sales_invoices,id'],
            'paymentDate' => ['required', 'date'],
            'paymentMethod' => ['required', 'in:'.implode(',', Payment::METHODS)],
            'paymentAmount' => ['required', 'numeric', 'gt:0'],
        ]);

        $invoice = SalesInvoice::with('payments')->findOrFail($this->payingInvoiceId);

        if (! $invoice->isPosted()) {
            return;
        }

        $amount = (float) $this->paymentAmount;
        $balance = $invoice->balance();

        if ($amount > $balance) {
            throw new \RuntimeException('Pembayaran melebihi sisa tagihan.');
        }

        Payment::create([
            'number' => app(NumberingService::class)->next('payment'),
            'payable_type' => SalesInvoice::class,
            'payable_id' => $invoice->id,
            'payment_date' => $this->paymentDate,
            'payment_method' => $this->paymentMethod,
            'reference' => $this->paymentReference ?: null,
            'amount' => $amount,
            'status' => Payment::STATUS_POSTED,
            'notes' => null,
            'created_by' => auth()->id(),
        ]);

        $newPaid = round((float) $invoice->paid_amount + $amount, 2);
        $invoice->update([
            'paid_amount' => $newPaid,
            'status' => round((float) $invoice->total - $newPaid, 2) <= 0 ? SalesInvoice::STATUS_PAID : SalesInvoice::STATUS_PARTIAL,
        ]);

        app(ActivityLogService::class)->log('payment', $invoice, "payment {$amount} received for invoice {$invoice->number}");

        $this->closePayment();

        session()->flash('status', "Pembayaran Rp {$amount} dicatat pada {$invoice->number}.");
    }

    protected function storeItems(SalesInvoice $invoice): void
    {
        $sales = app(SalesService::class);

        foreach ($this->items as $item) {
            $tax = $item['tax_id'] ? \App\Models\Tax::find($item['tax_id']) : null;

            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['product_name'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'],
                'tax_id' => $item['tax_id'],
                'line_total' => $sales->lineTotal($item['qty'], $item['unit_price'], $item['discount'], $tax),
            ]);
        }

        $invoice->update([
            'subtotal' => $invoice->items->sum(fn ($i) => (float) $i->qty * (float) $i->unit_price),
            'discount_amount' => $invoice->items->sum(fn ($i) => $sales->lineDiscount($i->qty, $i->unit_price, $i->discount)),
            'tax_amount' => $invoice->items->sum(fn ($i) => $sales->lineTax($i->qty, $i->unit_price, $i->discount, $i->tax)),
            'total' => $invoice->items->sum(fn ($i) => (float) $i->line_total),
        ]);
    }

    protected function recordApproval(SalesInvoice $invoice, string $action): void
    {
        Approval::create([
            'approvable_type' => SalesInvoice::class,
            'approvable_id' => $invoice->id,
            'approver_id' => auth()->id(),
            'action' => $action,
        ]);
    }
}
