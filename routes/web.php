<?php

use App\Livewire\Dashboard;
use App\Livewire\Inventory\StockAdjustment;
use App\Livewire\Inventory\StockOpnameComponent;
use App\Livewire\Inventory\StockTransferComponent;
use App\Livewire\Master\MasterCrud;
use App\Livewire\Notifications;
use App\Livewire\Product\ProductCrud;
use App\Livewire\Purchasing\GoodsReceiptComponent;
use App\Livewire\Purchasing\PurchaseInvoiceComponent;
use App\Livewire\Purchasing\PurchaseOrderComponent;
use App\Livewire\Reports;
use App\Livewire\Sales\DeliveryOrderComponent;
use App\Livewire\Sales\QuotationComponent;
use App\Livewire\Sales\SalesInvoiceComponent;
use App\Livewire\Sales\SalesOrderComponent;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::view('profile', 'profile')->name('profile');

    // Master Data
    Route::get('master/{entity}', MasterCrud::class)
        ->whereIn('entity', ['categories', 'brands', 'units', 'warehouses', 'taxes', 'customers', 'suppliers'])
        ->name('master.index');

    // Product
    Route::get('products', ProductCrud::class)->name('products.index');

    // Inventory
    Route::get('inventory/adjustments', StockAdjustment::class)->name('inventory.adjustments.index');
    Route::get('inventory/transfers', StockTransferComponent::class)->name('inventory.transfers.index');
    Route::get('inventory/opnames', StockOpnameComponent::class)->name('inventory.opnames.index');

    // Sales
    Route::get('sales/quotations', QuotationComponent::class)->name('sales.quotations.index');
    Route::get('sales/orders', SalesOrderComponent::class)->name('sales.orders.index');
    Route::get('sales/deliveries', DeliveryOrderComponent::class)->name('sales.deliveries.index');
    Route::get('sales/invoices', SalesInvoiceComponent::class)->name('sales.invoices.index');

    // Purchasing
    Route::get('purchasing/orders', PurchaseOrderComponent::class)->name('purchasing.orders.index');
    Route::get('purchasing/receipts', GoodsReceiptComponent::class)->name('purchasing.receipts.index');
    Route::get('purchasing/invoices', PurchaseInvoiceComponent::class)->name('purchasing.invoices.index');

    // Reports & Notifications
    Route::get('reports', Reports::class)->name('reports.index');
    Route::get('notifications', Notifications::class)->name('notifications.index');
});

require __DIR__.'/auth.php';
