# Deploy Laravel Inventory ke cPanel (Subdomain)

## Arsitektur
- **Domain target**: `https://app.evology-tech.xyz` (subdomain, document root → `public_html/inventory/public`)
- **Mengapa subdomain, bukan subfolder**: Livewire v3 memaksa URL root-relative (`/livewire/livewire.js`, `/livewire/update`). Di subfolder (mis. `/inventory/public`), path asset mengarah ke root domain → 404. Di subdomain, `/` langsung menunjuk ke `public/`, sehingga semua selaras.

## Strategi tanpa SSH
cPanel shared hosting ini **tidak punya terminal**. Semua langkah ditempuh lewat:
1. **File Manager** untuk upload/ekstrak zip, edit `.env`, buat `.htaccess`.
2. **Aplikasi kecil `public/probe.php`** untuk tugas yang membutuhkan PHP (perbaikan permission, rebuild storage, jalankan `migrate`/`seed`, baca log laravel).
3. **`php artisan migrate --force` & `db:seed --force`** dipanggil dari script web (bootstrap CLI kernel di dalam PHP).

## Persiapan Lokal (di PC developer)
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build   # hasil ke public/build
```
Kemudian zip seluruh project **tanpa**:
- `node_modules/`
- `.env` (jangan ikut deploy)
- `public/storage` (symlink/junction)
- folder `storage` isi proper strukturnya (lihat di bawah), bisa pakai `.gitkeep` sebagai placeholder.

## Upload & Layout
1. Upload zip ke `public_html/inventory/` dan ekstrak di File Manager.
2. Buat subdomain → document root arahkan ke `public_html/inventory/public`.
3. Buat database MySQL + user, catat nama DB/user/password.
4. Buat folder storage (jika belum ada), minimal:
   ```
   storage/app/public
   storage/app/private/livewire-tmp
   storage/framework/views
   storage/framework/cache/data
   storage/framework/sessions
   storage/framework/testing
   storage/logs
   ```

## Setelah Ekstrak (jarah wajib)
- **Chmod rekursif**: zip Windows/cPanel membuat direktori dengan mode `0644` (tanpa bit execute). Solusi script PHP: `dir=0755, file=0644`.
- **Rebuild storage** jika node `storage/app` atau `storage/framework` rusak / ber-mode 0644 (tampak seperti file). Rename dulu ke `storage.old`, lalu mkdir ulang via PHP dengan mode 0775.
- **Buat storage via PHP, bukan File Manager**: File Manager sering gagal membuat subfolder di sana ("Permission denied"). Pakai script PHP (euid = akun cPanel).

## Konfigurasi `.env` (server)
```
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:...   # wajib ada!
APP_DEBUG=false
APP_URL=https://app.evology-tech.xyz
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<isi_user_db>
DB_USERNAME=<isi_user_db>
DB_PASSWORD=<isi_password_db>

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true              # wajib karena mau akses via TLS

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

# (sisanya default: MAIL log, REDIS, AWS, dll.)
```
Peringatan:
- Jangan pakai `.env.example` bawaan — default masih `sqlite`, DB `root/laravel` semua sync.
- `SESSION_SECURE_COOKIE=true` harus diimbangi `APP_URL` https dan paksa link redirect. Kalau tidak, cookie secure tak tersimpan saat akses http → muncul "session expired".

## Migrasi & Seed (server tanpa SSH)
Buat `probe.php` di `public/`:
```php
<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();
$k->call('migrate', ['--force' => true]);
$k->call('db:seed', ['--force' => true]);
echo $returncode;
```
Buka lewat URL → sukses menghasilkan output artisan. Hapus `probe.php` setelah selesai.

## `.htaccess` (di public/) — gabungan https + front-controller
```apache
<IfModule mod_rewrite.c>
RewriteEngine On

# 1) Paksa HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# 2) Front-controller Laravel (default framework)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
</IfModule>
```
⚠️ Jangan memuat hanya redirect tanpa front-controller — semua laravel route menjadi 404 "Not Found".

## Diagnostik cepat (server, tanpa ssh)
Untuk menyetar DEBUG block menggunakan `public/probe.php`:
- Tampilkan mode file ke direktori vendor/storage.
- Baca `storage/logs/laravel.log` (extract seluruh pesan ERROR/CRITICAL).
- Boot aplikasi manual (`$app->make(Kernel::class)`) untuk melihat exception terawal.
- Melakukan chmod recursive / mkdir.
- Load handle request internal untuk cek status kode.

## Troubleshooting (untuk deploy case)
| Gejala | Penyebab | Solusi |
|---|---|---|
| `Permission denied` saat autoload php (vendor) | direktori vendor mode 0644 (tanpa x) | chmod recursive `dir=0755, file=0644` |
| Fatal dini + `laravel.log` tak terbuat | storage subfolders hilang / mode file 0644 | rebuild storage directory via PHP |
| `Missing App Key` / cipher | `.env` tanpa `APP_KEY` | generate base64 & tulis di .env |
| `no such table: settings (sqlite)` | DB_CONNECTION ikut sqlite | set `DB_CONNECTION=mysql` + kredensial |
| `Access denied user 'root'` | DB config default `.env.example` | isi DB_HOST/PORT/DATABASE/USERNAME/PASSWORD |
| Livewire 404 asset di subfolder | root-relative asset + subfolder path | gunakan subdomain |
| "Session expired / 419" beda perangkat | APP_URL http + `SESSION_SECURE_COOKIE=true` | APP_URL https + redirect https |
| 404 "Not Found" setelah htaccess | .htaccess hanya berisi redirect (hilang front-controller) | gabungkan kedua rule |

## Scheduler (Cron Jobs) + Verifikasi Pakai Heartbeat

**Task terjadwal** (`routes/console.php`):
- `expire-quotations` — tiap hari 00:10, menandai quotation draft/sent yang lewat `valid_until` menjadi expired.
- `inventory:notifications` — tiap 6 jam, menjalankan `inventory:notifications`.
- (disarankan) `heartbeat` — tiap menit menulis `now()` ke `storage/framework/heartbeat`, sebagai bukti scheduler benar-benar jalan.

Pasang di cPanel **Cron Jobs** (ini di-verify live):
```
* * * * * /usr/local/bin/php /home/evolo839/public_html/inventory/artisan schedule:run >> /home/evolo839/public_html/inventory/logs/cron.log 2>&1
```
> Path `/usr/local/bin/php` contoh. Pastikan path PHP CLI sesuai cPanel (lihat **MultiPHP Manager** / "Sistem ini" di Cron Jobs). Jika ragu, `#!/usr/local/bin/php echo` atau `which php` — karena tidak ada SSH, lahir dari sebagai probe `passthru('php artisan schedule:run')` untuk konfirmasi path.

### Verifikasi scheduler benar-benar jalan
Ciri khas **scheduler live**:
1. `cron.log` (`inventory/logs/cron.log`) berisi baris `Running [heartbeat] ... DONE` atau output dari task lain.
2. File `storage/framework/heartbeat` mtime tidak lebih dari ~1 menit vs server time.
3. Semua long job (error DB dsb.) secara output di `cron.log` atau `laravel.log`.

Script cek (bisa ditaruh di `probe.php`):
```php
<?php
header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL); ini_set('display_errors', 1);
$base = dirname(__DIR__);
$hb = "$base/storage/framework/heartbeat";
$cron = "$base/logs/cron.log";
echo "SEKARANG  : ", date('Y-m-d H:i:s'), "\n";
echo "heartbeat : ", (file_exists($hb) ? file_get_contents($hb) : 'BELUM ADA'), "\n";
echo "mtime     : ", (file_exists($hb) ? date('Y-m-d H:i:s', filemtime($hb)) : '-'), "\n";
echo "cron.log  : ", (file_exists($cron) ? substr(file_get_contents($cron), -1500) : 'BELUM ADA'), "\n";
```

**Tabel tanda kondisi:**
| Kondisi | Arti | Aksi |
|---|---|---|
| `heartbeat` mtime < 1 menit & updated | cron + schedule:run jalan normal | tidak ada |
| `cron.log` ADA berisi "Running [heartbeat] ... DONE" | schedule dieksekusi, task sukses | tidak ada |
| `cron.log` ADA berisi error/stack namun tetap | task error (miss. koneksi DB) | perbaiki task / config |
| `cron.log` & heartbeat ABSENT | cron tidak aktif / command path salah | periksa setup cron & path php |
| Heartbeat jalan tapi mtime selang (mis. 2 menit) atau STOP | write storage gagal / `storage` tidak writable | rebuild storage & permission 0775 |

Catatan timezone: heartbeat pakai timezone Laravel (`Asia/Jakarta`), sedangkan `date()` di probe memakai timezone PHP server (default UTC) — wajar berbeda 7 jam, bukan masalah.

## Cleanup setelah sukses
- Hapus `public/probe.php`.
- Hapus backup `storage.old` / `storage.old*`.
- Pastikan cron scheduler terpasang (lihat bagian Scheduler).
- User awal login: `admin@example.com` / `password`.

## Checklist Verifikasi
- [ ] `https://app.evology-tech.xyz/login` → 200 halaman login
- [ ] `/livewire/livewire.min.js` → 200
- [ ] `/build/manifest.json` → 200
- [ ] `/build/manifest.json` → 200
- [ ] Login berhasil di perangkat lain (tanpa pesan "expired").
- [ ] Filters `storage/framework/heartbeat` update tiap menit (scheduler jalan).
- [ ] `cron.log` berisi output schedule tanpa error.