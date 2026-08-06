<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckNotifications extends Command
{
    protected $signature = 'inventory:notifications';

    protected $description = 'Generate scheduled notifications (low stock, expired, overdue, pending)';

    public function handle(NotificationService $service): int
    {
        $this->lowStock($service);
        $this->expiredQuotations($service);
        $this->overdueInvoices($service);

        return self::SUCCESS;
    }

    protected function lowStock(NotificationService $service): void
    {
        $rows = Product::query()
            ->select('products.id', 'products.name', 'products.code', 'products.min_stock', 'products.reorder_point')
            ->selectRaw('COALESCE(SUM(product_warehouses.qty_on_hand), 0) as total_stock')
            ->leftJoin('product_warehouses', 'products.id', '=', 'product_warehouses.product_id')
            ->where('products.is_active', true)
            ->groupBy('products.id', 'products.name', 'products.code', 'products.min_stock', 'products.reorder_point')
            ->havingRaw('COALESCE(SUM(product_warehouses.qty_on_hand), 0) <= products.reorder_point')
            ->get();

        foreach ($rows as $row) {
            if ($this->alreadyNotifiedToday('low_stock', $row)) {
                continue;
            }

            $service->notify(
                'low_stock',
                'Stok menipis: '.$row->name,
                "Stok {$row->code} tinggal {$row->total_stock} (reorder point {$row->reorder_point}).",
                null,
                Product::find($row->id),
            );
        }
    }

    protected function expiredQuotations(NotificationService $service): void
    {
        $expired = Quotation::query()
            ->whereIn('status', [Quotation::STATUS_DRAFT, Quotation::STATUS_SENT])
            ->where('valid_until', '<', today())
            ->get();

        foreach ($expired as $quotation) {
            $quotation->update(['status' => Quotation::STATUS_EXPIRED]);

            $service->notify(
                'quotation_expired',
                'Quotation kedaluwarsa',
                "Quotation {$quotation->number} untuk {$quotation->customer?->name} telah kedaluwarsa.",
                null,
                $quotation,
            );
        }
    }

    protected function overdueInvoices(NotificationService $service): void
    {
        $sales = SalesInvoice::query()
            ->whereIn('status', [SalesInvoice::STATUS_POSTED, SalesInvoice::STATUS_PARTIAL])
            ->where('due_date', '<', today())
            ->get();

        foreach ($sales as $invoice) {
            if ($invoice->balance() <= 0) {
                continue;
            }

            if ($this->alreadyNotifiedToday('invoice_due', $invoice)) {
                continue;
            }

            $service->notify(
                'invoice_due',
                'Piutang jatuh tempo',
                "Invoice {$invoice->number} ({$invoice->customer?->name}) sisa {$invoice->balance()} melewati jatuh tempo.",
                null,
                $invoice,
            );
        }

        $purchases = PurchaseInvoice::query()
            ->whereIn('status', [PurchaseInvoice::STATUS_POSTED, PurchaseInvoice::STATUS_PARTIAL])
            ->where('due_date', '<', today())
            ->get();

        foreach ($purchases as $invoice) {
            if ($invoice->balance() <= 0) {
                continue;
            }

            if ($this->alreadyNotifiedToday('invoice_due', $invoice)) {
                continue;
            }

            $service->notify(
                'invoice_due',
                'Hutang jatuh tempo',
                "Invoice pembelian {$invoice->number} ({$invoice->supplier?->name}) sisa {$invoice->balance()} melewati jatuh tempo.",
                null,
                $invoice,
            );
        }
    }

    protected function alreadyNotifiedToday(string $type, $subject): bool
    {
        return DB::table('notifications')
            ->where('type', $type)
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->whereDate('created_at', today())
            ->exists();
    }
}
