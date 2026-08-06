<?php

namespace App\Livewire\Sales;

use App\Models\Approval;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\ActivityLogService;
use App\Services\SalesService;
use Livewire\Component;

class SalesOrderComponent extends Component
{
    public ?string $quotationId = null;

    public function render()
    {
        $orders = SalesOrder::with(['customer', 'items.product', 'quotation', 'invoice'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.sales.sales-order', [
            'orders' => $orders,
            'convertibleQuotations' => Quotation::with('customer')
                ->where('status', Quotation::STATUS_ACCEPTED)
                ->whereDoesntHave('salesOrder')
                ->orderByDesc('created_at')
                ->get(),
            'salesService' => app(SalesService::class),
        ])->title('Sales Order | Inventory System');
    }

    public function convert(): void
    {
        $this->validate([
            'quotationId' => ['required', 'exists:quotations,id'],
        ]);

        $quotation = Quotation::with('items')->findOrFail($this->quotationId);

        if (! $quotation->isConvertible()) {
            return;
        }

        $order = app(SalesService::class)->convertQuotationToOrder($quotation, auth()->id());

        app(ActivityLogService::class)->log('convert', $quotation, "converted quotation to order: {$order->number}");

        session()->flash('status', "Sales Order {$order->number} dibuat dari quotation.");

        $this->reset('quotationId');
    }

    public function approve(string $id): void
    {
        $order = SalesOrder::findOrFail($id);

        if ($order->status !== SalesOrder::STATUS_DRAFT) {
            return;
        }

        $order->update(['status' => SalesOrder::STATUS_APPROVED]);
        $this->recordApproval($order, 'approve');

        app(ActivityLogService::class)->log('approve', $order, "approved order: {$order->number}");
    }

    public function cancel(string $id): void
    {
        $order = SalesOrder::findOrFail($id);

        if ($order->status !== SalesOrder::STATUS_DRAFT) {
            return;
        }

        $order->update(['status' => SalesOrder::STATUS_CANCELLED]);
        $this->recordApproval($order, 'reject');

        app(ActivityLogService::class)->log('reject', $order, "cancelled order: {$order->number}");
    }

    protected function recordApproval(SalesOrder $order, string $action): void
    {
        Approval::create([
            'approvable_type' => SalesOrder::class,
            'approvable_id' => $order->id,
            'approver_id' => auth()->id(),
            'action' => $action,
        ]);
    }
}
