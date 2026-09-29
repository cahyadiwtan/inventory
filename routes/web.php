<?php

use App\Livewire\Dashboard;
use App\Livewire\DataTransfer;
use App\Livewire\Inventory\StockAdjustment;
use App\Livewire\Inventory\StockOpnameComponent;
use App\Livewire\Inventory\StockTransferComponent;
use App\Livewire\Master\CustomerFormComponent;
use App\Livewire\Master\MasterCrud;
use App\Livewire\Master\SupplierFormComponent;
use App\Livewire\Notifications;
use App\Livewire\Product\ProductCrud;
use App\Livewire\Purchasing\GoodsReceiptComponent;
use App\Livewire\Purchasing\PurchaseInvoiceComponent;
use App\Livewire\Purchasing\PurchaseOrderComponent;
use App\Livewire\Reports;
use App\Livewire\Settings\SettingsComponent;
use App\Livewire\System\UserManagement;
use App\Livewire\Sales\DeliveryOrderComponent;
use App\Livewire\Sales\DeliveryOrderDetailComponent;
use App\Livewire\Sales\DirectInvoiceComponent;
use App\Livewire\Sales\DirectInvoiceDetailComponent;
use App\Livewire\Sales\QuotationComponent;
use App\Livewire\Sales\QuotationCreateComponent;
use App\Livewire\Sales\QuotationDetailComponent;
use App\Livewire\Sales\QuotationEditComponent;
use App\Livewire\Sales\SalesInvoiceComponent;
use App\Livewire\Sales\SalesInvoiceDetailComponent;
use App\Livewire\Sales\SalesOrderComponent;
use App\Livewire\Sales\SalesOrderDetailComponent;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::view('profile', 'profile')->name('profile');

    // Master Data
    Route::get('master/{entity}', MasterCrud::class)
        ->whereIn('entity', ['categories', 'brands', 'units', 'warehouses', 'taxes', 'customers', 'suppliers'])
        ->name('master.index');
    Route::get('master/customers/create', CustomerFormComponent::class)->name('master.customers.create');
    Route::get('master/customers/{customer}/edit', CustomerFormComponent::class)->name('master.customers.edit');
    Route::get('master/suppliers/create', SupplierFormComponent::class)->name('master.suppliers.create');
    Route::get('master/suppliers/{supplier}/edit', SupplierFormComponent::class)->name('master.suppliers.edit');

    // Product
    Route::get('products', ProductCrud::class)->name('products.index');

    // Inventory
    Route::get('inventory/adjustments', StockAdjustment::class)->name('inventory.adjustments.index');
    Route::get('inventory/transfers', StockTransferComponent::class)->name('inventory.transfers.index');
    Route::get('inventory/opnames', StockOpnameComponent::class)->name('inventory.opnames.index');

    // Sales
    Route::get('sales/quotations', QuotationComponent::class)->name('sales.quotations.index');
    Route::get('sales/quotations/create', QuotationCreateComponent::class)->name('sales.quotations.create');
    Route::get('sales/quotations/{quotation}/edit', QuotationEditComponent::class)->name('sales.quotations.edit');
    Route::get('sales/quotations/{quotation}', QuotationDetailComponent::class)->name('sales.quotations.show');
    Route::get('sales/orders', SalesOrderComponent::class)->name('sales.orders.index');
    Route::get('sales/orders/{salesOrder}', SalesOrderDetailComponent::class)->name('sales.orders.show');
    Route::get('sales/deliveries', DeliveryOrderComponent::class)->name('sales.deliveries.index');
    Route::get('sales/deliveries/{deliveryOrder}', DeliveryOrderDetailComponent::class)->name('sales.deliveries.show');
    Route::get('sales/invoices', SalesInvoiceComponent::class)->name('sales.invoices.index');
    Route::get('sales/invoices/{invoice}', SalesInvoiceDetailComponent::class)->name('sales.invoices.show');
    Route::get('sales/direct-invoices', DirectInvoiceComponent::class)->name('sales.direct-invoices.index');
    Route::get('sales/direct-invoices/{invoice}', DirectInvoiceDetailComponent::class)->name('sales.direct-invoices.show');

    // Purchasing
    Route::get('purchasing/orders', PurchaseOrderComponent::class)->name('purchasing.orders.index');
    Route::get('purchasing/receipts', GoodsReceiptComponent::class)->name('purchasing.receipts.index');
    Route::get('purchasing/invoices', PurchaseInvoiceComponent::class)->name('purchasing.invoices.index');

    // Reports & Notifications
    Route::get('reports', Reports::class)->name('reports.index');
    Route::get('notifications', Notifications::class)->name('notifications.index');
    Route::get('data-transfer', DataTransfer::class)->name('data-transfer.index');

    // Settings
    Route::get('settings', SettingsComponent::class)->name('settings.index');

    // System
    Route::get('system/users', UserManagement::class)->name('users.index');
});

require __DIR__.'/auth.php';
