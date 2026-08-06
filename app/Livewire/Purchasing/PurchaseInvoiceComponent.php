<?php

namespace App\Livewire\Purchasing;

use App\Models\Approval;
use App\Models\GoodsReceipt;
use App\Models\Payment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Services\ActivityLogService;
use App\Services\NumberingService;
use App\Services\PurchaseService;
use Livewire\Component;

class PurchaseInvoiceComponent extends Component
{
    public ?string $purchaseOrderId = null;

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
        $invoices = PurchaseInvoice::with(['purchaseOrder.supplier', 'supplier', 'items.product', 'payments'])
            ->orderByDesc('created_at')
            ->get();

        $invoicableOrders = PurchaseOrder::with(['supplier', 'items'])
            ->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_RECEIVED])
            ->whereDoesntHave('invoice')
            ->get()
            ->filter(fn (PurchaseOrder $order) => $order->goodsReceipts()
                ->where('status', GoodsReceipt::STATUS_POSTED)->exists());

        return view('livewire.purchasing.purchase-invoice', [
            'invoices' => $invoices,
            'invoicableOrders' => $invoicableOrders,
            'purchaseService' => app(PurchaseService::class),
        ])->title('Purchase Invoice | Inventory System');
    }

    public function updatedPurchaseOrderId($value): void
    {
        $this->items = [];

        if (! $value) {
            return;
        }

        $order = PurchaseOrder::with('items.product')->find($value);

        if (! $order) {
            return;
        }

        $purchase = app(PurchaseService::class);

        foreach ($order->items as $item) {
            $received = $purchase->receivedQty($item);

            if ($received <= 0) {
                continue;
            }

            $this->items[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'qty' => $received,
                'unit_price' => (float) $item->unit_price,
                'discount' => (float) $item->discount,
                'tax_id' => $item->tax_id,
            ];
        }

        if ($order->supplier && $order->supplier->payment_term_days) {
            $this->dueDate = now()->addDays($order->supplier->payment_term_days)->toDateString();
        }
    }

    public function create(): void
    {
        $this->validate([
            'purchaseOrderId' => ['required', 'exists:purchase_orders,id'],
            'invoiceDate' => ['required', 'date'],
            'dueDate' => ['required', 'date', 'after_or_equal:invoiceDate'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
        ]);

        $order = PurchaseOrder::findOrFail($this->purchaseOrderId);

        $invoice = PurchaseInvoice::create([
            'number' => app(NumberingService::class)->next('purchase_invoice'),
            'purchase_order_id' => $order->id,
            'supplier_id' => $order->supplier_id,
            'invoice_date' => $this->invoiceDate,
            'due_date' => $this->dueDate,
            'status' => PurchaseInvoice::STATUS_DRAFT,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        $this->storeItems($invoice);

        $this->recordApproval($invoice, 'submit');

        app(ActivityLogService::class)->log('submit', $invoice, "created purchase invoice: {$invoice->number}");

        session()->flash('status', "Invoice {$invoice->number} dibuat.");

        $this->reset(['purchaseOrderId', 'notes']);
        $this->items = [];
    }

    public function post(string $id): void
    {
        $invoice = PurchaseInvoice::findOrFail($id);

        if ($invoice->status !== PurchaseInvoice::STATUS_DRAFT) {
            return;
        }

        $invoice->update(['status' => PurchaseInvoice::STATUS_POSTED]);
        $this->recordApproval($invoice, 'post');

        app(ActivityLogService::class)->log('post', $invoice, "posted purchase invoice: {$invoice->number}");
    }

    public function openPayment(string $id): void
    {
        $this->payingInvoiceId = $id;
        $invoice = PurchaseInvoice::findOrFail($id);
        $this->paymentAmount = (string) $invoice->balance();
    }

    public function closePayment(): void
    {
        $this->reset(['payingInvoiceId', 'paymentAmount', 'paymentReference']);
    }

    public function submitPayment(): void
    {
        $this->validate([
            'payingInvoiceId' => ['required', 'exists:purchase_invoices,id'],
            'paymentDate' => ['required', 'date'],
            'paymentMethod' => ['required', 'in:'.implode(',', Payment::METHODS)],
            'paymentAmount' => ['required', 'numeric', 'gt:0'],
        ]);

        $invoice = PurchaseInvoice::with('payments')->findOrFail($this->payingInvoiceId);

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
            'payable_type' => PurchaseInvoice::class,
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
            'status' => round((float) $invoice->total - $newPaid, 2) <= 0 ? PurchaseInvoice::STATUS_PAID : PurchaseInvoice::STATUS_PARTIAL,
        ]);

        app(ActivityLogService::class)->log('payment', $invoice, "payment {$amount} sent for invoice {$invoice->number}");

        $this->closePayment();

        session()->flash('status', "Pembayaran Rp {$amount} dicatat pada {$invoice->number}.");
    }

    protected function storeItems(PurchaseInvoice $invoice): void
    {
        $purchase = app(PurchaseService::class);

        foreach ($this->items as $item) {
            $tax = $item['tax_id'] ? \App\Models\Tax::find($item['tax_id']) : null;

            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['product_name'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'],
                'tax_id' => $item['tax_id'],
                'line_total' => $purchase->lineTotal($item['qty'], $item['unit_price'], $item['discount'], $tax),
            ]);
        }

        $invoice->update([
            'subtotal' => $invoice->items->sum(fn ($i) => (float) $i->qty * (float) $i->unit_price),
            'discount_amount' => $invoice->items->sum(fn ($i) => $purchase->lineDiscount($i->qty, $i->unit_price, $i->discount)),
            'tax_amount' => $invoice->items->sum(fn ($i) => $purchase->lineTax($i->qty, $i->unit_price, $i->discount, $i->tax)),
            'total' => $invoice->items->sum(fn ($i) => (float) $i->line_total),
        ]);
    }

    protected function recordApproval(PurchaseInvoice $invoice, string $action): void
    {
        Approval::create([
            'approvable_type' => PurchaseInvoice::class,
            'approvable_id' => $invoice->id,
            'approver_id' => auth()->id(),
            'action' => $action,
        ]);
    }
}
