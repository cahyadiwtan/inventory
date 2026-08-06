# Database ERD

```mermaid
erDiagram
    USERS ||--o{ ACTIVITY_LOGS : "melakukan"
    USERS ||--o{ APPROVALS : "menyetujui"
    ROLES ||--o{ ROLE_HAS_PERMISSIONS : ""
    PERMISSIONS ||--o{ ROLE_HAS_PERMISSIONS : ""
    USERS ||--o{ MODEL_HAS_ROLES : ""
    USERS ||--o{ MODEL_HAS_PERMISSIONS : ""
    USERS ||--o{ NOTIFICATIONS : ""

    PRODUCT_CATEGORIES ||--o{ PRODUCTS : "memiliki"
    PRODUCT_BRANDS ||--o{ PRODUCTS : "memiliki"
    UNITS ||--o{ PRODUCTS : "satuan"
    PRODUCTS ||--o{ PRODUCT_WAREHOUSES : "stock per gudang"
    WAREHOUSES ||--o{ PRODUCT_WAREHOUSES : ""
    PRODUCTS ||--o{ PRODUCT_BARCODES : ""
    PRODUCTS ||--o{ PRODUCT_PRICES : ""
    PRODUCTS ||--o{ STOCK_MOVEMENTS : "sumber mutasi"

    SUPPLIERS ||--o{ PURCHASE_ORDERS : ""
    PURCHASE_ORDERS ||--o{ PURCHASE_ORDER_ITEMS : ""
    SUPPLIERS ||--o{ PURCHASE_INVOICES : ""
    PURCHASE_INVOICES ||--o{ PURCHASE_INVOICE_ITEMS : ""
    PURCHASE_ORDERS ||--o{ GOODS_RECEIPTS : ""
    GOODS_RECEIPTS ||--o{ GOODS_RECEIPT_ITEMS : ""
    GOODS_RECEIPTS ||--o{ STOCK_MOVEMENTS : "menerima"

    CUSTOMERS ||--o{ QUOTATIONS : ""
    CUSTOMERS ||--o{ SALES_ORDERS : ""
    QUOTATIONS ||--o{ QUOTATION_ITEMS : ""
    QUOTATIONS |o--o{ SALES_ORDERS : "converted"
    SALES_ORDERS ||--o{ SALES_ORDER_ITEMS : ""
    SALES_ORDERS ||--o{ DELIVERY_ORDERS : ""
    DELIVERY_ORDERS ||--o{ DELIVERY_ORDER_ITEMS : ""
    DELIVERY_ORDERS ||--o{ STOCK_MOVEMENTS : "mengirim"
    SALES_ORDERS ||--o{ SALES_INVOICES : ""
    SALES_INVOICES ||--o{ SALES_INVOICE_ITEMS : ""
    CUSTOMERS ||--o{ SALES_INVOICES : ""

    SALES_INVOICES ||--o{ PAYMENTS : "pembayaran piutang"
    PURCHASE_INVOICES ||--o{ PAYMENTS : "pembayaran hutang"
    PRODUCTS ||--o{ STOCK_ADJUSTMENTS : ""
    STOCK_ADJUSTMENTS ||--o{ STOCK_MOVEMENTS : ""
    STOCK_TRANSFERS ||--o{ STOCK_TRANSFER_ITEMS : ""
    STOCK_TRANSFERS ||--o{ STOCK_MOVEMENTS : ""
    STOCK_OPNAMES ||--o{ STOCK_OPNAME_ITEMS : ""
    STOCK_OPNAME_ITEMS ||--o{ STOCK_ADJUSTMENTS : "selisih"
    APPROVALS ||--o{ STOCK_TRANSFERS : ""
    APPROVALS ||--o{ STOCK_OPNAMES : ""
    APPROVALS ||--o{ PURCHASE_ORDERS : ""
```

## Ringkasan Tabel Utama

| Tabel | Tujuan |
|-------|--------|
| product_warehouses | Saldo stok per produk per gudang (qty_on_hand) |
| stock_movements | Ledger mutasi stok (in/out) dengan reference_type |
| quotations / quotation_items | Dokumen penawaran |
| sales_orders / sales_order_items | Pesanan penjualan |
| delivery_orders / delivery_order_items | Pengiriman barang |
| sales_invoices / sales_invoice_items | Faktur penjualan |
| purchase_orders / purchase_order_items | Pesanan pembelian |
| goods_receipts / goods_receipt_items | Penerimaan barang |
| purchase_invoices / purchase_invoice_items | Faktur pembelian |
| payments | Pembayaran (polymorphic payable: sales/purchase invoice) |
| stock_adjustments | Penyesuaian stok |
| stock_transfers / stock_transfer_items | Transfer antar gudang |
| stock_opnames / stock_opname_items | Stock opname |
| approvals | Approval log (polymorphic approvable) |
| activity_logs | Audit trail semua aksi |
