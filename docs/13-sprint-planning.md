# Sprint Planning

## Sprint Durasi: 1 minggu / sprint. Total 6 sprint.

### Sprint 1 — Foundation & Master Data
- [ ] Scaffold & struktur clean architecture
- [ ] UUID trait, activity log service, numbering service
- [ ] Breeze + Spatie role/permission + seeder roles
- [ ] CRUD Category, Brand, Unit, Warehouse, Tax
- **Deliverable:** modul master data + feature tests hijau
- **Review:** demo CRUD & auth, permission checks

### Sprint 2 — Customer, Supplier, Product
- [x] CRUD Customer & Supplier
- [x] CRUD Product + multi barcode + multi price
- [x] Stock on hand per warehouse (product_warehouses)
- [ ] Import/export produk dasar
- **Deliverable:** product + partner master + tests
- **Review:** demo product dengan multi warehouse stock

### Sprint 3 — Inventory Core
- [x] StockService + stock_movements ledger
- [x] Stock Adjustment (plus/minus + reason)
- [x] Stock Opname (scan, import excel, approval, selisih)
- [x] Stock Transfer (request→approve→transfer→receive)
- **Deliverable:** seluruh inventory workflow + tests
- **Review:** demo transfer antar gudang & opname selisih

### Sprint 4 — Sales Pipeline
- [x] Quotation (status lifecycle, expired scheduler)
- [x] Convert → Sales Order
- [x] Picking → Delivery Order (post stok OUT)
- [x] Sales Invoice + payment (partial/multi)
- **Deliverable:** sales end-to-end + tests
- **Review:** demo SO→DO→Invoice→Payment, outstanding

### Sprint 5 — Purchasing Pipeline
- [x] Purchase Order + approval
- [x] Goods Receive (post stok IN)
- [x] Purchase Invoice + payment
- **Deliverable:** purchasing end-to-end + tests
- **Review:** demo PO→GRN→P.Invoice→Payment

### Sprint 6 — Reporting, Dashboard, Notifications, Hardening
- [x] 15 laporan + export Excel/PDF
- [x] Dashboard stat + grafik
- [x] Notifikasi terjadwal (stock min, expired, due, pending)
- [x] QA penuh, index/query pass, seed produksi
- **Deliverable:** release-ready
- **Review:** uji penerimaan menyeluruh

## Kriteria Keluar (Definition of Done)
1. Feature test & unit test hijau untuk modul.
2. Semua aksi tercatat di activity log.
3. Tidak ada query N+1 (checked).
4. Dokumen numerisasi konsisten.
5. UI berfungsi sesuai wireframe.
