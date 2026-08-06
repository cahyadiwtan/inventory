# Folder Structure (Clean Architecture)

```
D:\ADI
├── app/
│   ├── Http/
│   │   ├── Controllers/            # Web controller tipis (delegasi ke service)
│   │   │   └── Api/                # (opsional) API resource controllers
│   │   ├── Middleware/
│   │   ├── Requests/               # Form Request Validation
│   │   │   ├── Product/
│   │   │   ├── Customer/
│   │   │   ├── Quotation/
│   │   │   └── ...
│   │   └── Resources/              # API Resource (JSON transform)
│   ├── Livewire/                   # Livewire component (class-based)
│   │   ├── Dashboard/
│   │   ├── Master/
│   │   ├── Product/
│   │   ├── Inventory/
│   │   ├── Purchasing/
│   │   ├── Sales/
│   │   └── Report/
│   ├── Models/                     # Eloquent model (UUID)
│   │   ├── Concerns/
│   │   │   ├── UsesUuid.php
│   │   │   └── HasActivityLog.php
│   ├── Repositories/               # Repository Pattern
│   │   ├── Contracts/              # Interface
│   │   │   ├── ProductRepositoryInterface.php
│   │   │   └── ...
│   │   └── Eloquent/
│   │       ├── ProductRepository.php
│   │       └── ...
│   ├── Services/                   # Business logic (Service Pattern)
│   │   ├── Stock/
│   │   │   ├── StockService.php
│   │   │   ├── StockMovementService.php
│   │   │   ├── StockAdjustmentService.php
│   │   │   ├── StockTransferService.php
│   │   │   └── StockOpnameService.php
│   │   ├── Sales/
│   │   │   ├── QuotationService.php
│   │   │   ├── SalesOrderService.php
│   │   │   ├── DeliveryOrderService.php
│   │   │   ├── SalesInvoiceService.php
│   │   ├── Purchase/
│   │   │   ├── PurchaseOrderService.php
│   │   │   ├── GoodsReceiptService.php
│   │   │   └── PurchaseInvoiceService.php
│   │   ├── PaymentService.php
│   │   ├── NumberingService.php
│   │   ├── ActivityLogService.php
│   │   └── DashboardService.php
│   ├── Support/
│   │   └── (helpers, macro)
│   ├── Events/                     # Event
│   │   ├── StockMoved.php
│   │   ├── OrderConverted.php
│   │   ├── InvoicePaid.php
│   │   └── ...
│   ├── Listeners/                  # Listener
│   │   ├── LogActivity.php
│   │   ├── NotifyStockLow.php
│   │   └── ...
│   ├── Policies/                   # Authorization Policy
│   ├── Notifications/
│   │   ├── StockLowNotification.php
│   │   ├── QuotationExpiredNotification.php
│   │   ├── InvoiceDueNotification.php
│   │   └── ...
│   └── Jobs/
│       ├── ExportReportJob.php
│       ├── ImportProductsJob.php
│       └── CheckDueNotificationsJob.php
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   ├── seeders/
│   │   ├── RoleSeeder.php
│   │   ├── PermissionSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── MasterDataSeeder.php
│   │   └── ...
├── docs/                           # 15 dokumen perencanaan
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── layouts/
│       ├── components/
│       ├── auth/
│       ├── dashboard/
│       ├── master/
│       ├── product/
│       ├── inventory/
│       ├── purchasing/
│       ├── sales/
│       ├── report/
│       └── livewire/
│           └── volt/               # Volt functional components
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Unit/
│   ├── Feature/
│   │   ├── Master/
│   │   ├── Product/
│   │   ├── Inventory/
│   │   ├── Purchasing/
│   │   └── Sales/
└── ...
```

## Prinsip

1. **Controller** → hanya menerima request, memanggil service, mengembalikan response.
2. **Repository** → abstraksi akses data (interface + implementasi Eloquent).
3. **Service** → transaksi bisnis, validasi domain, pemanggilan event.
4. **Form Request** → validasi input.
5. **Policy** → otorisasi per model.
6. **Event/Listener** → efek samping (audit log, notifikasi).
