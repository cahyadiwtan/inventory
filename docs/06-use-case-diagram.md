# Use Case Diagram

```mermaid
flowchart LR
    SA[Super Admin] --- UM[Mengelola User & Role]
    MGR[Manager] --- APP[Approve transaksi]
    MGR --- DASH[Melihat Dashboard]
    WH[Warehouse] --- ADJ[Stock Adjustment]
    WH --- OPN[Stock Opname]
    WH --- TRF[Stock Transfer]
    WH --- DO[Proses Delivery Order]
    PUR[Purchasing] --- PO[Buat Purchase Order]
    PUR --- GRN[Goods Receive]
    PUR --- PINV[Purchase Invoice]
    SAL[Sales] --- QUO[Quotation]
    SAL --- SO[Sales Order]
    SAL --- SINV[Sales Invoice]
    FIN[Finance] --- PAY[Payment]
    FIN --- OUT[Outstanding Monitoring]
    VW[Viewer] --- RPT[Laporan]

    SA --- ACCT[Mengelola Master Data]
    MGR --- ACCT
    SA --- ROLE[Sederet Roles & Permissions]
```

## Detail Use Case Penting

| UC | Actor | Deskripsi |
|----|-------|-----------|
| UC-01 | Sales | Membuat quotation dari draft, menambah item, submit ke open |
| UC-02 | Sales/Manager | Convert quotation ter-approve menjadi Sales Order |
| UC-03 | Sales | Membuat SO manual, input item, qty, harga, diskon, pajak |
| UC-04 | Warehouse | Melakukan picking & membuat Delivery Order |
| UC-05 | Finance | Membuat Sales Invoice dari SO, mencatat partial payment |
| UC-06 | Purchasing | Membuat PO dari PR, memantau status penerimaan |
| UC-07 | Warehouse | Menerima barang (GRN), sistem menambah stok |
| UC-08 | Warehouse | Melakukan stock opname dengan scan/import |
| UC-09 | Manager | Menyetujui/menolak stock transfer, opname, PO |
| UC-10 | All | Membaca laporan & melihat notifikasi |
