# Asumsi & Batasan

## Asumsi
1. **MariaDB/MySQL 8+** tersedia (dev memakai MariaDB 12.2 di port 3308).
2. **PHP 8.4+** dan **Composer 2** terpasang.
3. Autentikasi hanya untuk user internal (Breeze, bukan API publik).
4. Mata uang **IDR (Rp)** untuk semua harga.
5. PPN dihitung dari satu tax rate per dokumen (bisa 0 = non-PKP).
6. Satu produk memiliki satu unit dasar (konversi unit antar-satuan di luar scope v1).
7. Pembulatan 2 desimal untuk qty & harga.
8. Approver adalah user dengan role Manager (atau dengan permission approve).
9. Notifikasi dikirim via database notification + email (log mailer di dev).
10. Redis opsional di dev; production menggunakan Redis untuk cache & queue.

## Batasan (Out of Scope v1)
1. **Multi-currency** — hanya IDR.
2. **BOM / Manufacturing / Assembling** — tidak ada.
3. **Serial number / batch / expiry produk** — tidak dilacak per batch.
4. **E-commerce / POS / marketplace** — tidak terhubung.
5. **Integrasi akuntansi umum (GL/Jurnal)** — invoice dan payment dicatat, tidak mem-booking jurnal double-entry.
6. **Multi-company / multi-tenant** — satu perusahaan.
7. **Unit conversion & packaging** — satu satuan per produk.
8. **Budgeting / approval multi-level kompleks** — approval satu level per dokumen.
9. **Scheduled report email** — manual export (dapat ditambah).
10. **Offline mode / PWA** — tidak disediakan.
11. **Barcode printing template** — scan untuk opname; printing label tidak termasuk v1.
12. **Scan barcode via kamera** — input barcode manual/keyboard wedge; scan kamera dapat ditambah.

## Ketergantungan
- Laravel 12, PHP 8.4, Livewire 3 (Volt), TailwindCSS, Flowbite.
- Spatie Laravel Permission, Laravel Excel, DomPDF, Redis.
- Nginx + PHP-FPM (production Linux).

## Keputusan Terbuka (untuk dikonfirmasi user)
1. Perlu approval ganda untuk PO di atas nilai tertentu? (default: 1 level)
2. Invoice diposting otomatis setelah DO shipped, atau manual? (default: manual)
3. Perlu fitur credit limit di-enforce (blokir SO jika melebihi)? (default: enforce dengan warning)
