# Daftar Risiko & Mitigasi

| # | Risiko | Prob | Impact | Mitigasi |
|---|--------|------|--------|----------|
| 1 | **Stok negatif** akibat race condition | Tinggi | Tinggi | Semua mutasi dalam DB transaction + `WHERE qty_on_hand >= qty` (optimistic lock), atau lock row. |
| 2 | **Nomor duplikat** saat concurrent create | Sedang | Tinggi | Counter bernomor di lock (atomic update) dalam transaksi; unique index pada kolom number. |
| 3 | **Stok tidak sinkron** (opname vs ledger) | Sedang | Tinggi | Stock hanya berubah via StockService; opname memicu adjustment resmi. |
| 4 | **Double posting** dokumen | Sedang | Tinggi | Guard status transisi di service (state machine), idempotent guard di method post. |
| 5 | **Kinerja menurun** pada laporan besar | Sedang | Sedang | Index kolom filter (created_at, warehouse, product), Redis cache, pagination, export via queue. |
| 6 | **Permission salah** (role over-access) | Sedang | Tinggi | Policy per model + seeder permission yang didefinisikan; test authorization. |
| 7 | **Data sensitive bocor** (NPWP, harga) | Rendah | Tinggi | Auth enforced di middleware, gate semua resource, env production secure. |
| 8 | **Import excel rusak/partial** | Sedang | Sedang | Import di-transaction per baris, validasi kolom, import report untuk baris gagal. |
| 9 | **Dependency vulnerability** | Rendah | Tinggi | `composer audit` di CI, update rutin. |
| 10 | **Redis tidak tersedia** di produksi | Sedang | Sedang | Fallback ke database cache/queue; dokumentasi instalasi Redis. |
| 11 | **Perubahan requirement workflow** | Sedang | Sedang | State machine terpusat di service, mudah dimodifikasi; sprint review. |
| 12 | **Migration conflict antar developer** | Rendah | Sedang | Satu arah migration, konvensi penamaan, fresh-migrate di CI. |
| 13 | **Deploy Nginx di Linux gagal** | Sedang | Sedang | Dokumentasi deploy lengkap, storage link, permission, supervisor queue worker. |
| 14 | **Timezone/lokal (IDR, indonesian)** | Rendah | Sedang | Locale id, currency format, timezone Asia/Jakarta sejak awal. |

## Asumsi Kapasitas (v1)
- Target user concurrent: < 100.
- Volume transaksi: < 5.000/hari.
- Single app server + single DB server (scale out nanti).
