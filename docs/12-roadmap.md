# Roadmap Development

## Tahapan

### Fase 0 — Foundation (Minggu 1)
- Scaffold Laravel 12 + stack
- Clean Architecture & struktur folder
- UUID, audit trail, numbering service
- Breeze auth + Spatie roles/permissions

### Fase 1 — Master Data (Minggu 1–2)
- Product Category, Brand, Unit, Warehouse, Tax
- Customer, Supplier
- Product (multi-warehouse/barcode/price, image)

### Fase 2 — Core Inventory (Minggu 2–3)
- StockService (mutasi aman)
- Stock Adjustment
- Stock Opname (scan, import, approval)
- Stock Transfer (workflow 4 langkah)

### Fase 3 — Sales Pipeline (Minggu 3–4)
- Quotation → convert → Sales Order
- Delivery Order / Picking
- Sales Invoice + Partial/Multi payment

### Fase 4 — Purchasing Pipeline (Minggu 4–5)
- Purchase Order → Goods Receive → Purchase Invoice → Payment

### Fase 5 — Reporting & Analytics (Minggu 5–6)
- 15 laporan + export Excel/PDF
- Dashboard stat & grafik
- Notifikasi terjadwal

### Fase 6 — Hardening (Minggu 6)
- Full test suite, audit keamanan
- Seed data produksi, dokumentasi deploy Nginx/Linux
- Performance pass (query index, Redis cache)

## Milestone
| M | Kriteria |
|---|----------|
| M1 | Auth + master data selesai & tested |
| M2 | Inventory core (adjustment/opname/transfer) selesai |
| M3 | Sales & purchase pipeline berfungsi end-to-end |
| M4 | Laporan + dashboard + notifikasi |
| M5 | Production-ready (deploy docs, seed, hardening) |
