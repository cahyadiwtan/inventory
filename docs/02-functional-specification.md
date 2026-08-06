# Functional Specification Document (FSD)

## 1. Entitas & Field Detail

### 1.1 Product Category
| Field | Tipe | Wajib | Catatan |
|-------|------|-------|---------|
| code | string(50), unique | Ya | Kode kategori |
| name | string(100) | Ya | Nama kategori |
| is_active | bool | Ya | Status |

### 1.2 Product Brand
| Field | Tipe | Wajib |
|-------|------|-------|
| code | string(50), unique | Ya |
| name | string(100) | Ya |
| is_active | bool | Ya |

### 1.3 Unit
| Field | Tipe | Wajib |
|-------|------|-------|
| code | string(20), unique | Ya |
| name | string(50) | Ya |
| symbol | string(10) | Ya |

### 1.4 Warehouse
| Field | Tipe | Wajib |
|-------|------|-------|
| code | string(50), unique | Ya |
| name | string(100) | Ya |
| address | text | Tidak |
| phone | string(30) | Tidak |
| is_active | bool | Ya |

### 1.5 Tax
| Field | Tipe | Wajib |
|-------|------|-------|
| code | string(20), unique | Ya |
| name | string(100) | Ya |
| rate | decimal(8,2) | Ya | Persen PPN dll. |
| is_active | bool | Ya |

### 1.6 Customer / Supplier (shared fields)
| Field | Tipe | Wajib |
|-------|------|-------|
| code | string(50), unique | Ya |
| name | string(150) | Ya |
| npwp | string(30) | Tidak |
| address | text | Ya |
| phone | string(30) | Tidak |
| email | string(100) | Tidak |
| pic_name | string(100) | Tidak |
| payment_term_days | int | Ya | Default 30 |
| credit_limit | decimal(16,2) | Ya | Default 0 |
| is_active | bool | Ya |

### 1.7 Product
| Field | Tipe | Wajib |
|-------|------|-------|
| code | string(50), unique | Ya |
| barcode | string(100) | Tidak (bisa banyak via product_barcodes) |
| name | string(150) | Ya |
| category_id | FK | Ya |
| brand_id | FK | Tidak |
| unit_id | FK | Ya |
| selling_price | decimal(16,2) | Ya |
| purchase_price | decimal(16,2) | Ya |
| min_stock | decimal(16,2) | Ya |
| max_stock | decimal(16,2) | Ya |
| reorder_point | decimal(16,2) | Ya |
| weight | decimal(10,3) | Tidak |
| image_path | string | Tidak |
| is_active | bool | Ya |

Relasi:
- **product_warehouses** (pivot + qty on hand per gudang)
- **product_barcodes** (multi barcode per produk)
- **product_prices** (multi price: type + price)

## 2. Alur Nomor Otomatis
Format: `PREFIX-YYYYMM-NNNNNN` (counter per prefix per bulan).
Service `NumberingService::next(string $prefix): string`.

## 3. Status Mesin (State Machine)

### Quotation: `draft → open → approved | rejected | expired`, `approved → converted`
### Sales Order: `draft → open → picking | confirmed → delivered → invoiced | void`
### Delivery Order: `draft → picked → shipped → received`
### Invoice (Sales/Purchase): `draft → posted → partially_paid → paid | void`
### Stock Transfer: `requested → approved | rejected → transferred → received`
### Stock Opname: `draft → submitted → approved | rejected → posted`

## 4. Aturan Bisnis Kunci

1. **Stock hanya boleh berubah** melalui `StockMovement` (single source of truth).
2. Qty tidak boleh melebihi stock on hand untuk transaksi minus (kecuali diizinkan setting).
3. Pengurangan stok terjadi pada event posting (DO / GRN), bukan saat draft.
4. Invoice hanya dapat dibuat dari dokumen approved (SO → Invoice, GRN → Purchase Invoice).
5. Credit limit customer dicek saat SO/invoice di-posting.
6. Quotation expired otomatis menjadi status `expired` via scheduler.
7. Multi payment: total paid dihitung dari payment records; status `paid` bila outstanding = 0.
8. Adjustment wajib memiliki reason.
9. Opname menghasilkan selisih yang memicu stock adjustment bertipe opname.
10. Semua dokumen sekali diposting tidak dapat diedit; harus di-void.

## 5. Notifikasi (Scheduled)
| Trigger | Kondisi | Penerima |
|---------|---------|----------|
| Stock minimum | qty_on_hand <= min_stock | Warehouse, Manager |
| Quotation expired | expired_date < today && status open | Sales |
| Invoice jatuh tempo | due_date < today && outstanding > 0 | Finance |
| PO belum diterima | expected_date < today && status open | Purchasing |
| SO belum dikirim | requested_date < today && status confirmed | Warehouse |

## 6. Persetujuan (Approval)
Setiap workflow approval dicatat dengan: approver, status, notes, timestamp.
Menggunakan tabel `approvals` (polymorphic) agar reusable.

## 7. Export/Import
- Import: Master Product & Stock Opname (Excel) — via job queue + import report.
- Export: Semua laporan (Excel) dan dokumen (PDF via DomPDF).
