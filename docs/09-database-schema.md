# Database Schema (DDL Ringkas)

Semua tabel utama menggunakan **UUID** primary key, soft delete, dan timestamp.
Format tipe mengikuti MySQL/MariaDB.

## users
```
uuid PK, name, email unique, password, email_verified_at, remember_token,
is_active bool default true, timestamps
```

## roles / permissions / model_has_roles / model_has_permissions / role_has_permissions
Standar Spatie Permission (UUID columns untuk model_has_*).

## product_categories
```
uuid PK, code unique, name, is_active bool, softDeletes, timestamps
```

## product_brands
```
uuid PK, code unique, name, is_active bool, softDeletes, timestamps
```

## units
```
uuid PK, code unique, name, symbol, softDeletes, timestamps
```

## warehouses
```
uuid PK, code unique, name, address text, phone, is_active bool, softDeletes, timestamps
```

## taxes
```
uuid PK, code unique, name, rate decimal(8,2), is_active bool, softDeletes, timestamps
```

## customers / suppliers
```
uuid PK, code unique, name, npwp, address text, phone, email, pic_name,
payment_term_days int default 30, credit_limit decimal(16,2) default 0,
is_active bool, softDeletes, timestamps
```

## products
```
uuid PK, code unique, barcode, name, category_id FK, brand_id FK nullable,
unit_id FK, selling_price decimal(16,2), purchase_price decimal(16,2),
min_stock decimal(16,2), max_stock decimal(16,2), reorder_point decimal(16,2),
weight decimal(10,3) nullable, image_path nullable, is_active bool,
softDeletes, timestamps
```

## product_warehouses
```
uuid PK, product_id FK, warehouse_id FK, qty_on_hand decimal(16,2) default 0,
unique(product_id, warehouse_id)
```

## product_barcodes
```
uuid PK, product_id FK, barcode unique
```

## product_prices
```
uuid PK, product_id FK, price_type enum('selling','purchase','special'), price decimal(16,2),
valid_from, valid_to nullable
```

## stock_movements
```
uuid PK, product_id FK, warehouse_id FK, movement_type enum('in','out'),
reference_type polymorphic (e.g. App\Models\GoodsReceipt),
reference_id, qty decimal(16,2), qty_before decimal(16,2), qty_after decimal(16,2),
reason nullable, created_by FK users, timestamps
```

## quotations
```
uuid PK, number unique, customer_id FK, sales_id FK nullable,
expired_date date, status enum(draft,open,approved,rejected,expired,converted),
subtotal, discount_amount, tax_amount, grand_total decimal(16,2),
notes, created_by, timestamps, softDeletes
```

## quotation_items
```
uuid PK, quotation_id FK, product_id FK, qty decimal, price decimal,
discount decimal(16,2), line_total decimal(16,2)
```

## sales_orders
```
uuid PK, number unique, quotation_id FK nullable, customer_id FK, sales_id FK nullable,
order_date date, status enum(draft,open,picking,delivered,invoiced,void),
subtotal, discount_amount, tax_amount, tax_id FK nullable, grand_total decimal(16,2),
created_by, timestamps, softDeletes
```

## sales_order_items
```
uuid PK, sales_order_id FK, product_id FK, qty decimal, price decimal,
discount decimal, tax_rate decimal(8,2), line_total decimal(16,2)
```

## delivery_orders
```
uuid PK, number unique, sales_order_id FK, warehouse_id FK,
status enum(draft,picked,shipped,received), shipped_date, received_date,
created_by, timestamps, softDeletes
```

## delivery_order_items
```
uuid PK, delivery_order_id FK, product_id FK, qty decimal
```

## sales_invoices
```
uuid PK, number unique, sales_order_id FK, customer_id FK,
invoice_date, due_date, status enum(draft,posted,partially_paid,paid,void),
subtotal, discount_amount, tax_amount, grand_total, created_by, timestamps, softDeletes
```

## sales_invoice_items
```
uuid PK, sales_invoice_id FK, product_id FK, qty, price, line_total decimal(16,2)
```

## purchase_orders
```
uuid PK, number unique, purchase_request_id FK nullable, supplier_id FK,
order_date, expected_date nullable, status enum(draft,open,approved,rejected,received,invoiced,void),
subtotal, tax_amount, grand_total decimal(16,2), created_by, timestamps, softDeletes
```

## purchase_order_items
```
uuid PK, purchase_order_id FK, product_id FK, qty, price decimal, line_total decimal(16,2)
```

## goods_receipts
```
uuid PK, number unique, purchase_order_id FK, warehouse_id FK,
received_date, status enum(draft,posted), created_by, timestamps, softDeletes
```

## goods_receipt_items
```
uuid PK, goods_receipt_id FK, product_id FK, qty decimal
```

## purchase_invoices
```
uuid PK, number unique, purchase_order_id FK, supplier_id FK,
invoice_date, due_date, status enum(draft,posted,partially_paid,paid,void),
subtotal, tax_amount, grand_total, created_by, timestamps, softDeletes
```

## purchase_invoice_items
```
uuid PK, purchase_invoice_id FK, product_id FK, qty, price, line_total decimal(16,2)
```

## payments
```
uuid PK, number unique, payable_type polymorphic (sales_invoices|purchase_invoices),
payable_id, amount decimal(16,2), paid_at date, method string, reference nullable,
created_by, timestamps
```

## stock_adjustments
```
uuid PK, number unique, warehouse_id FK, type enum('plus','minus'),
reason text (wajib), status enum(draft,posted), created_by, timestamps, softDeletes
```

## stock_adjustment_items
```
uuid PK, stock_adjustment_id FK, product_id FK, qty decimal
```

## stock_transfers
```
uuid PK, number unique, from_warehouse_id FK, to_warehouse_id FK,
status enum(requested,approved,rejected,transferred,received), notes,
created_by, timestamps, softDeletes
```

## stock_transfer_items
```
uuid PK, stock_transfer_id FK, product_id FK, qty decimal
```

## stock_opnames
```
uuid PK, number unique, warehouse_id FK, status enum(draft,submitted,approved,rejected,posted),
opname_date, notes, created_by, timestamps, softDeletes
```

## stock_opname_items
```
uuid PK, stock_opname_id FK, product_id FK, system_qty decimal, actual_qty decimal, difference decimal
```

## approvals
```
uuid PK, approvable_type polymorphic, approvable_id, approver_id FK users,
action enum(approve,reject,submit,post,void), notes, created_at
```

## activity_logs
```
uuid PK, user_id FK nullable, event string (login, create, update, delete, approve, print, export, import, void, restore),
subject_type polymorphic, subject_id, properties json, ip, user_agent, created_at
```

## notifications
Tabel standar Laravel.
