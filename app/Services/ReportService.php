<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function definitions(): array
    {
        return [
            'stock_on_hand' => ['label' => 'Stok per Gudang', 'group' => 'Stok', 'warehouse' => true],
            'stock_movement' => ['label' => 'Ledger Mutasi Stok', 'group' => 'Stok', 'range' => true],
            'low_stock' => ['label' => 'Stok Menipis', 'group' => 'Stok'],
            'stock_adjustment' => ['label' => 'Penyesuaian Stok', 'group' => 'Stok', 'range' => true],
            'stock_transfer' => ['label' => 'Transfer Stok', 'group' => 'Stok', 'range' => true],
            'stock_opname' => ['label' => 'Stock Opname', 'group' => 'Stok', 'range' => true],
            'sales_order' => ['label' => 'Sales Order', 'group' => 'Penjualan', 'range' => true],
            'delivery_order' => ['label' => 'Delivery Order', 'group' => 'Penjualan', 'range' => true],
            'sales_invoice' => ['label' => 'Faktur Penjualan', 'group' => 'Penjualan', 'range' => true],
            'sales_by_product' => ['label' => 'Penjualan per Produk', 'group' => 'Penjualan', 'range' => true],
            'sales_by_customer' => ['label' => 'Penjualan per Customer', 'group' => 'Penjualan', 'range' => true],
            'purchase_order' => ['label' => 'Purchase Order', 'group' => 'Pembelian', 'range' => true],
            'goods_receipt' => ['label' => 'Penerimaan Barang', 'group' => 'Pembelian', 'range' => true],
            'purchase_invoice' => ['label' => 'Faktur Pembelian', 'group' => 'Pembelian', 'range' => true],
            'payments' => ['label' => 'Pembayaran', 'group' => 'Keuangan', 'range' => true],
        ];
    }

    public function groups(): array
    {
        $groups = [];
        foreach ($this->definitions() as $key => $def) {
            $groups[$def['group']][] = ['key' => $key, 'label' => $def['label']];
        }

        return $groups;
    }

    public function run(string $key, array $filters = []): array
    {
        return match ($key) {
            'stock_on_hand' => $this->stockOnHand($filters),
            'stock_movement' => $this->stockMovement($filters),
            'low_stock' => $this->lowStock(),
            'stock_adjustment' => $this->stockAdjustment($filters),
            'stock_transfer' => $this->stockTransfer($filters),
            'stock_opname' => $this->stockOpname($filters),
            'sales_order' => $this->salesOrder($filters),
            'delivery_order' => $this->deliveryOrder($filters),
            'sales_invoice' => $this->salesInvoice($filters),
            'sales_by_product' => $this->salesByProduct($filters),
            'sales_by_customer' => $this->salesByCustomer($filters),
            'purchase_order' => $this->purchaseOrder($filters),
            'goods_receipt' => $this->goodsReceipt($filters),
            'purchase_invoice' => $this->purchaseInvoice($filters),
            'payments' => $this->payments($filters),
            default => throw new \InvalidArgumentException("Laporan tidak dikenal: {$key}"),
        };
    }

    protected function range($query, string $column, array $filters): void
    {
        if (! empty($filters['from'])) {
            $query->whereDate($column, '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate($column, '<=', $filters['to']);
        }
    }

    protected function money(float $value): string
    {
        return number_format($value, 2);
    }

    protected function stockOnHand(array $filters): array
    {
        $query = ProductWarehouse::query()
            ->join('products', 'product_warehouses.product_id', '=', 'products.id')
            ->join('warehouses', 'product_warehouses.warehouse_id', '=', 'warehouses.id')
            ->select('products.code', 'products.name', 'warehouses.name as warehouse', 'product_warehouses.qty_on_hand', 'products.min_stock', 'products.reorder_point');

        if (! empty($filters['warehouse'])) {
            $query->where('product_warehouses.warehouse_id', $filters['warehouse']);
        }

        $rows = $query->orderBy('warehouses.name')->orderBy('products.name')->get()
            ->map(fn ($r) => [
                $r->code, $r->name, $r->warehouse, number_format((float) $r->qty_on_hand, 2),
                number_format((float) $r->min_stock, 2), number_format((float) $r->reorder_point, 2),
            ])->all();

        return ['title' => 'Stok per Gudang', 'headings' => ['Produk', 'Nama', 'Gudang', 'Stok', 'Min', 'Reorder'], 'rows' => $rows];
    }

    protected function stockMovement(array $filters): array
    {
        $query = StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->join('warehouses', 'stock_movements.warehouse_id', '=', 'warehouses.id')
            ->select('stock_movements.*', 'products.code', 'products.name as product_name', 'warehouses.name as warehouse');

        $this->range($query, 'stock_movements.created_at', $filters);

        $rows = $query->orderByDesc('stock_movements.created_at')->limit(1000)->get()
            ->map(fn ($r) => [
                $r->created_at?->format('d M Y H:i'), $r->code, $r->product_name, $r->warehouse,
                strtoupper($r->movement_type), $this->money((float) $r->qty),
                $this->money((float) $r->qty_before), $this->money((float) $r->qty_after),
                $r->reason ?? class_basename((string) $r->reference_type),
            ])->all();

        return ['title' => 'Ledger Mutasi Stok', 'headings' => ['Tanggal', 'Kode', 'Produk', 'Gudang', 'Tipe', 'Qty', 'Sebelum', 'Sesudah', 'Referensi'], 'rows' => $rows];
    }

    protected function lowStock(): array
    {
        $rows = Product::query()
            ->select('products.code', 'products.name', 'products.min_stock', 'products.reorder_point')
            ->selectRaw('COALESCE(SUM(product_warehouses.qty_on_hand), 0) as total_stock')
            ->leftJoin('product_warehouses', 'products.id', '=', 'product_warehouses.product_id')
            ->where('products.is_active', true)
            ->groupBy('products.id', 'products.code', 'products.name', 'products.min_stock', 'products.reorder_point')
            ->havingRaw('COALESCE(SUM(product_warehouses.qty_on_hand), 0) <= products.reorder_point')
            ->orderByRaw('total_stock ASC')
            ->get()
            ->map(fn ($r) => [
                $r->code, $r->name, $this->money((float) $r->total_stock),
                $this->money((float) $r->min_stock), $this->money((float) $r->reorder_point),
            ])->all();

        return ['title' => 'Stok Menipis', 'headings' => ['Kode', 'Produk', 'Total Stok', 'Min', 'Reorder'], 'rows' => $rows];
    }

    protected function stockAdjustment(array $filters): array
    {
        $query = StockAdjustment::query()
            ->withSum('items as total_qty', 'qty')
            ->with('warehouse');

        $this->range($query, 'created_at', $filters);

        $rows = $query->orderByDesc('created_at')->get()
            ->map(fn ($r) => [
                $r->number, $r->warehouse?->name, strtoupper($r->type),
                $r->created_at?->format('d M Y'), ucfirst($r->status), $this->money((float) ($r->total_qty ?? 0)),
            ])->all();

        return ['title' => 'Penyesuaian Stok', 'headings' => ['Nomor', 'Gudang', 'Tipe', 'Tanggal', 'Status', 'Total Qty'], 'rows' => $rows];
    }

    protected function stockTransfer(array $filters): array
    {
        $query = StockTransfer::query()
            ->withSum('items as total_qty', 'qty')
            ->with(['fromWarehouse', 'toWarehouse']);

        $this->range($query, 'created_at', $filters);

        $rows = $query->orderByDesc('created_at')->get()
            ->map(fn ($r) => [
                $r->number, $r->fromWarehouse?->name, $r->toWarehouse?->name,
                $r->created_at?->format('d M Y'), ucfirst($r->status), $this->money((float) ($r->total_qty ?? 0)),
            ])->all();

        return ['title' => 'Transfer Stok', 'headings' => ['Nomor', 'Asal', 'Tujuan', 'Tanggal', 'Status', 'Total Qty'], 'rows' => $rows];
    }

    protected function stockOpname(array $filters): array
    {
        $query = StockOpname::query()
            ->withSum('items as total_difference', 'difference')
            ->with('warehouse');

        $this->range($query, 'created_at', $filters);

        $rows = $query->orderByDesc('created_at')->get()
            ->map(fn ($r) => [
                $r->number, $r->warehouse?->name, $r->opname_date?->format('d M Y'),
                ucfirst($r->status), $this->money((float) ($r->total_difference ?? 0)),
            ])->all();

        return ['title' => 'Stock Opname', 'headings' => ['Nomor', 'Gudang', 'Tanggal', 'Status', 'Selisih'], 'rows' => $rows];
    }

    protected function salesOrder(array $filters): array
    {
        $query = SalesOrder::query()->with('customer');

        $this->range($query, 'order_date', $filters);

        $rows = $query->orderByDesc('order_date')->get()
            ->map(fn ($r) => [
                $r->number, $r->customer?->name, $r->order_date?->format('d M Y'),
                $this->money((float) $r->total), ucfirst($r->status),
            ])->all();

        return ['title' => 'Sales Order', 'headings' => ['Nomor', 'Customer', 'Tanggal', 'Total', 'Status'], 'rows' => $rows];
    }

    protected function deliveryOrder(array $filters): array
    {
        $query = DeliveryOrder::query()->with(['salesOrder.customer', 'warehouse']);

        $this->range($query, 'delivery_date', $filters);

        $rows = $query->orderByDesc('delivery_date')->get()
            ->map(fn ($r) => [
                $r->number, $r->salesOrder?->number, $r->salesOrder?->customer?->name,
                $r->warehouse?->name, $r->delivery_date?->format('d M Y'), ucfirst($r->status),
            ])->all();

        return ['title' => 'Delivery Order', 'headings' => ['Nomor', 'SO', 'Customer', 'Gudang', 'Tanggal', 'Status'], 'rows' => $rows];
    }

    protected function salesInvoice(array $filters): array
    {
        $query = SalesInvoice::query()->with('customer');

        $this->range($query, 'invoice_date', $filters);

        $rows = $query->orderByDesc('invoice_date')->get()
            ->map(fn ($r) => [
                $r->number, $r->customer?->name, $r->invoice_date?->format('d M Y'),
                $r->due_date?->format('d M Y'), $this->money((float) $r->total),
                $this->money((float) $r->paid_amount), $this->money($r->balance()), ucfirst($r->status),
            ])->all();

        return ['title' => 'Faktur Penjualan', 'headings' => ['Nomor', 'Customer', 'Tanggal', 'Jatuh Tempo', 'Total', 'Dibayar', 'Sisa', 'Status'], 'rows' => $rows];
    }

    protected function salesByProduct(array $filters): array
    {
        $query = DB::table('sales_invoice_items')
            ->join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('products', 'sales_invoice_items.product_id', '=', 'products.id')
            ->select('products.code', 'products.name')
            ->selectRaw('SUM(sales_invoice_items.qty) as qty, SUM(sales_invoice_items.line_total) as omzet')
            ->groupBy('products.id', 'products.code', 'products.name');

        $this->range($query, 'sales_invoices.invoice_date', $filters);

        $rows = $query->orderByDesc('omzet')->limit(100)->get()
            ->map(fn ($r) => [
                $r->code, $r->name, $this->money((float) $r->qty), $this->money((float) $r->omzet),
            ])->all();

        return ['title' => 'Penjualan per Produk', 'headings' => ['Kode', 'Produk', 'Qty Terjual', 'Omzet'], 'rows' => $rows];
    }

    protected function salesByCustomer(array $filters): array
    {
        $query = SalesInvoice::query()->with('customer');

        $this->range($query, 'invoice_date', $filters);

        $rows = $query->get()
            ->groupBy('customer_id')
            ->map(fn ($items) => [
                $items->first()->customer?->name,
                $this->money($items->sum(fn ($i) => (float) $i->total)),
                $this->money($items->sum(fn ($i) => (float) $i->paid_amount)),
                $this->money($items->sum(fn ($i) => $i->balance())),
            ])->values()->all();

        return ['title' => 'Penjualan per Customer', 'headings' => ['Customer', 'Total Tagihan', 'Dibayar', 'Sisa'], 'rows' => $rows];
    }

    protected function purchaseOrder(array $filters): array
    {
        $query = PurchaseOrder::query()->with('supplier');

        $this->range($query, 'order_date', $filters);

        $rows = $query->orderByDesc('order_date')->get()
            ->map(fn ($r) => [
                $r->number, $r->supplier?->name, $r->order_date?->format('d M Y'),
                $this->money((float) $r->total), ucfirst($r->status),
            ])->all();

        return ['title' => 'Purchase Order', 'headings' => ['Nomor', 'Supplier', 'Tanggal', 'Total', 'Status'], 'rows' => $rows];
    }

    protected function goodsReceipt(array $filters): array
    {
        $query = GoodsReceipt::query()->with(['purchaseOrder.supplier', 'warehouse']);

        $this->range($query, 'receipt_date', $filters);

        $rows = $query->orderByDesc('receipt_date')->get()
            ->map(fn ($r) => [
                $r->number, $r->purchaseOrder?->number, $r->purchaseOrder?->supplier?->name,
                $r->warehouse?->name, $r->receipt_date?->format('d M Y'), ucfirst($r->status),
            ])->all();

        return ['title' => 'Penerimaan Barang', 'headings' => ['Nomor', 'PO', 'Supplier', 'Gudang', 'Tanggal', 'Status'], 'rows' => $rows];
    }

    protected function purchaseInvoice(array $filters): array
    {
        $query = PurchaseInvoice::query()->with('supplier');

        $this->range($query, 'invoice_date', $filters);

        $rows = $query->orderByDesc('invoice_date')->get()
            ->map(fn ($r) => [
                $r->number, $r->supplier?->name, $r->invoice_date?->format('d M Y'),
                $r->due_date?->format('d M Y'), $this->money((float) $r->total),
                $this->money((float) $r->paid_amount), $this->money($r->balance()), ucfirst($r->status),
            ])->all();

        return ['title' => 'Faktur Pembelian', 'headings' => ['Nomor', 'Supplier', 'Tanggal', 'Jatuh Tempo', 'Total', 'Dibayar', 'Sisa', 'Status'], 'rows' => $rows];
    }

    protected function payments(array $filters): array
    {
        $query = Payment::query()->with('payable');

        $this->range($query, 'payment_date', $filters);

        $rows = $query->orderByDesc('payment_date')->limit(1000)->get()
            ->map(function ($r) {
                $partner = '';
                if ($r->payable instanceof SalesInvoice) {
                    $partner = $r->payable->customer?->name.' (Piutang)';
                } elseif ($r->payable instanceof PurchaseInvoice) {
                    $partner = $r->payable->supplier?->name.' (Hutang)';
                }

                return [
                    $r->payment_date?->format('d M Y'), $r->number, $partner,
                    ucfirst(str_replace('_', ' ', $r->payment_method)), $r->reference ?: '-',
                    $this->money((float) $r->amount),
                ];
            })->all();

        return ['title' => 'Pembayaran', 'headings' => ['Tanggal', 'Nomor', 'Mitra', 'Metode', 'Referensi', 'Jumlah'], 'rows' => $rows];
    }
}
