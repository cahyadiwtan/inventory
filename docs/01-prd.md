# PRD — Sistem Inventory, Quotation & Invoice

## 1. Ringkasan Eksekutif

Sistem Inventory Management berbasis web untuk mengelola siklus bisnis penuh:
**Master Data → Inventory → Pembelian → Penjualan → Invoice → Laporan → Dashboard**.
Dibangun dengan Laravel 12, Livewire 3 (Volt), TailwindCSS, dan MySQL/MariaDB.

## 2. Tujuan

| # | Tujuan | Metrik Keberhasilan |
|---|--------|---------------------|
| 1 | Sentralisasi data master barang, customer, supplier | Semua entitas tersimpan di 1 sumber data |
| 2 | Akurasi stock multi-gudang secara real-time | Selisih stock opname < 1% |
| 3 | Otomasi workflow purchasing & sales | Waktu proses transaksi turun 40% |
| 4 | Traceability penuh (audit trail) | Semua aksi tercatat dengan actor & timestamp |
| 5 | Laporan analitik & keputusan | 14+ jenis laporan & dashboard real-time |

## 3. Persona & Role

| Role | Deskripsi | Kewenangan inti |
|------|-----------|-----------------|
| Super Admin | Mengelola sistem & user | Semua akses |
| Manager | Menyetujui & memantau | Approve, laporan, dashboard |
| Warehouse | Kelola stok | Adjustment, Opname, Transfer, DO |
| Purchasing | Siklus pembelian | PR, PO, GRN, Purchase Invoice |
| Sales | Siklus penjualan | Quotation, SO, Picking, DO, Invoice |
| Finance | Pembayaran & piutang/hutang | Payment, outstanding |
| Viewer | Lihat saja | Dashboard & laporan |

## 4. Cakupan Fungsional (Modul)

### 4.1 Master Data
- Product Category, Product Brand, Unit, Warehouse, Tax (CRUD)
- Customer & Supplier (CRUD, dengan NPWP, PIC, payment term, credit limit, status)
- Product (multi-warehouse stock, multi-barcode, multi-price, image)

### 4.2 Inventory
- Stock Adjustment (Plus/Minus, wajib alasan)
- Stock Opname (scan barcode, import excel, approval, selisih)
- Stock Transfer (Request → Approval → Transfer → Receive)

### 4.3 Purchasing
`Purchase Request → Purchase Order → Goods Receive → Purchase Invoice → Payment`

### 4.4 Sales
`Quotation → Sales Order → Picking → Delivery Order → Sales Invoice → Payment`

### 4.5 Reporting
Inventory, Stock Card, Stock Movement, Sales, Purchase, Quotation, Invoice, Profit,
Customer, Supplier, Top Product, Slow Moving, Fast Moving, Dead Stock, Inventory Valuation.

### 4.6 Dashboard & Notification
- Stat cards: Total Produk, Customer, Supplier, Nilai Inventory, Penjualan/Pembelian bulan ini
- Grafik: Penjualan, Pembelian, Inventory, Cash Flow, Top Customer, Top Product, Warehouse Utilization
- Notifikasi: Stock minimum, Quotation expired, Invoice jatuh tempo, PO belum diterima, SO belum dikirim

## 5. Kebutuhan Non-Fungsional

| Aspek | Persyaratan |
|-------|-------------|
| Keamanan | Breeze auth, Spatie RBAC, Policy, Form Request, soft delete, UUID |
| Performa | Redis cache, queue untuk export/import & notifikasi |
| Ketersediaan | Nginx + Linux production, MySQL/MariaDB |
| Audit | Activity log untuk semua aksi (login, CRUD, approve, print, export, import, void, restore) |
| Standar | PSR-12, SOLID, Repository + Service pattern, Clean Architecture |

## 6. Prioritas (MoSCoW)

| Prioritas | Item |
|-----------|------|
| **Must** | Master data, Product, Stock Adjustment, SO/DO/Invoice, PO/GRN, Laporan dasar, Auth+Roles |
| **Should** | Stock Opname, Stock Transfer, Quotation→SO convert, Partial/Multi payment, Dashboard grafik |
| **Could** | Notifikasi real-time, Import excel, Top/Slow/Fast/Dead report |
| **Won't (v1)** | POS, e-commerce, integrasi akuntansi pihak ketiga |

## 7. Kriteria Penerimaan Umum
1. Semua transaksi berjalan dalam database transaction.
2. Setiap mutasi stok menghasilkan record stock movement dengan reference.
3. Setiap dokumen bernomor otomatis sesuai format (QT-, SO-, DO-, INV-, PO-, GRN-).
4. Semua modul memiliki feature test.
5. Soft delete tersedia pada seluruh master data.
6. Aktivitas penting tercatat di activity logs.

## 8. Tanggal & Owner
- Product Owner: (user)
- Dokumen ini hidup dan dapat direvisi per sprint.
