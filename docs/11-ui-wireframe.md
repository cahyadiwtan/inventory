# UI Wireframe

## Layout Umum (Admin)

```
┌──────────────┬──────────────────────────────────────────────┐
│  Sidebar     │  Topbar                                       │
│  (Deep Navy) │  [Search]                     [Notif][Avatar] │
│  ──────────  ├──────────────────────────────────────────────┤
│  Dashboard   │                                              │
│  Master Data │           CONTENT AREA                       │
│    Category  │                                              │
│    Brand     │                                              │
│    Unit      │                                              │
│    Warehouse │                                              │
│    Tax       │                                              │
│  Customers   │                                              │
│  Suppliers   │                                              │
│  Products    │                                              │
│  Inventory   │                                              │
│    Adjust    │                                              │
│    Opname    │                                              │
│    Transfer  │                                              │
│  Purchasing  │                                              │
│    PO        │                                              │
│    GRN       │                                              │
│    P. Invoice│                                              │
│  Sales       │                                              │
│    Quotation │                                              │
│    SO        │                                              │
│    DO        │                                              │
│    S. Invoice│                                              │
│  Reports     │                                              │
│  Users       │                                              │
└──────────────┴──────────────────────────────────────────────┘
```

## Dashboard

```
┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐
│ Produk │ │Customer│ │Supplier│ │Nilai Inv│
│  125   │ │   40   │ │   25   │ │Rp 850jt │
└────────┘ └────────┘ └────────┘ └────────┘
┌────────┐ ┌────────┐ ┌──────────┐ ┌──────────┐
│Penjualan│ │Pembelian│ │Hampir Habis│ │Tidak Bergerak│
│ Rp 120jt│ │ Rp 80jt │ │   8 item   │ │   12 item   │
└────────┘ └────────┘ └──────────┘ └──────────┘
┌───────────────────────┐ ┌───────────────────────┐
│   Grafik Penjualan    │ │  Grafik Pembelian     │
│     (line chart)      │ │     (line chart)      │
└───────────────────────┘ └───────────────────────┘
┌─────────────────────────────────────────────────┐
│            Grafik Inventory (bar/area)          │
└─────────────────────────────────────────────────┘
```

## Product Index

```
┌─[+ Tambah Produk]─────────────[Cari: ____]────────┐
│ Kode │ Nama     │ Kategori │ Unit │ Harga Jual │ Stok │ Status │ Aksi │
│ PRD-1│ Kaos     │ Apparel  │ pcs  │ 50.000     │ 120  │ Aktif  │ [E][D]│
│ PRD-2│ Kemeja   │ Apparel  │ pcs  │ 75.000     │ 5    │ Aktif  │ [E][D]│
└─────────────────────────────────────────────────────┘
```

## Dokumen (Quotation / SO / Invoice)

```
┌───────────────────────────────────────────────┐
│ Nomor: QT-202608-000001        Status: OPEN   │
│ Customer: PT Maju Jaya   Sales: Budi          │
│ Expired: 2026-08-20                          │
├───────────────────────────────────────────────┤
│ # │ Produk    │ Qty │ Price │ Disc │ Line     │
│ 1 │ Kaos      │ 10  │ 50000 │ 0    │ 500.000  │
├───────────────────────────────────────────────┤
│ Subtotal: 500.000   Disc: 0   Tax: 50.000     │
│ GRAND TOTAL: 550.000                          │
│ [Submit] [Approve] [Convert to SO]            │
└───────────────────────────────────────────────┘
```

## Stock Opname (Scan)

```
┌──────────────────────────────────────────────┐
│ Stock Opname #OPN-202608-000001  [Import Excel]│
│ Warehouse: Gudang A    [Scan Barcode: _____]  │
├──────────────────────────────────────────────┤
│ Barcode │ Produk   │ System │ Actual │ Selisih│
│ 899999  │ Kaos     │  120   │  118   │   -2   │
└──────────────────────────────────────────────┘
                [Submit untuk Approval]
```

## Laporan / Export

```
[Laporan: Stock Card]  [Warehouse: Semua] [Dari][Sampai]
[Export Excel] [Export PDF]
┌──────────────┬────────┬──────────┬───────────┬─────────┐
│ Tanggal      │ Tipe   │ Referensi│ Masuk     │ Keluar  │
│ 2026-08-01   │ IN     │ GRN-...  │ 100       │ 0       │
│ 2026-08-03   │ OUT    │ DO-...   │ 0         │ 20      │
└──────────────┴────────┴──────────┴───────────┴─────────┘
```

## Notifikasi (dropdown)

```
🔔
├─ Stock Min: Kaos (stok 5)          • 2 jam
├─ Quotation QT-... expired          • 5 jam
├─ Invoice INV-... jatuh tempo       • 1 hari
└─ PO PO-... belum diterima          • 2 hari
```
