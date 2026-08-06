<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard', [
            'totalProducts' => Product::where('is_active', true)->count(),
            'stockValue' => (float) ProductWarehouse::join('products', 'product_warehouses.product_id', '=', 'products.id')
                ->sum(DB::raw('product_warehouses.qty_on_hand * products.purchase_price')),
            'openOrders' => SalesOrder::where('status', SalesOrder::STATUS_APPROVED)->count(),
            'receivable' => (float) SalesInvoice::whereIn('status', [SalesInvoice::STATUS_POSTED, SalesInvoice::STATUS_PARTIAL])
                ->selectRaw('SUM(total - paid_amount) as total')->value('total'),
            'payable' => (float) PurchaseInvoice::whereIn('status', [PurchaseInvoice::STATUS_POSTED, PurchaseInvoice::STATUS_PARTIAL])
                ->selectRaw('SUM(total - paid_amount) as total')->value('total'),
            'lowStocks' => Product::select('products.id', 'products.code', 'products.name', 'products.reorder_point')
                ->selectRaw('COALESCE(SUM(product_warehouses.qty_on_hand), 0) as total_stock')
                ->leftJoin('product_warehouses', 'products.id', '=', 'product_warehouses.product_id')
                ->where('products.is_active', true)
                ->groupBy('products.id', 'products.code', 'products.name', 'products.reorder_point')
                ->havingRaw('COALESCE(SUM(product_warehouses.qty_on_hand), 0) <= products.reorder_point')
                ->orderByRaw('total_stock ASC')
                ->limit(8)
                ->get(),
            'salesTrend' => $this->salesTrend(),
            'warehouseStocks' => Warehouse::where('is_active', true)
                ->withSum('stockRows', 'qty_on_hand')
                ->orderByDesc('stock_rows_sum_qty_on_hand')
                ->limit(6)
                ->get(),
            'recentActivities' => ActivityLog::with('subject')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ])->title('Dashboard | Inventory System');
    }

    protected function salesTrend(): array
    {
        $rows = DB::table('sales_orders')
            ->where('status', SalesOrder::STATUS_APPROVED)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $byMonth = $rows->pluck('total', 'month');

        $out = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $out[] = [
                'month' => now()->subMonths($i)->format('M'),
                'total' => round((float) ($byMonth[$month] ?? 0), 2),
            ];
        }

        return $out;
    }
}
