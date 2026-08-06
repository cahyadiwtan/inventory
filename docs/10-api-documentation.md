# API Documentation

## Konvensi
- Base URL: `/api/v1`
- Auth: Laravel Sanctum (Bearer Token)
- Format: JSON (API Resource)
- Pagination: `?page=` → `{ "data": [...], "meta": { ... } }`

## Auth
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | /api/v1/auth/login | Login, return token |
| POST | /api/v1/auth/logout | Logout (revoke token) |
| GET | /api/v1/auth/me | Profil user + roles + permissions |

## Master Data
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET/POST | /api/v1/categories | List / create category |
| GET/PUT/DELETE | /api/v1/categories/{id} | Detail / update / delete |
| GET/POST | /api/v1/brands | List / create brand |
| GET/POST | /api/v1/units | List / create unit |
| GET/POST | /api/v1/warehouses | List / create warehouse |
| GET/POST | /api/v1/taxes | List / create tax |
| GET/POST | /api/v1/customers | List / create customer |
| GET/POST | /api/v1/suppliers | List / create supplier |

## Product
| Method | Endpoint |
|--------|----------|
| GET/POST | /api/v1/products |
| GET/PUT/DELETE | /api/v1/products/{id} |
| GET | /api/v1/products/{id}/stock |
| POST | /api/v1/products/{id}/barcodes |
| POST | /api/v1/products/{id}/prices |
| GET | /api/v1/products/by-barcode/{barcode} |

## Inventory
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET/POST | /api/v1/stock/adjustments | List / create adjustment |
| POST | /api/v1/stock/adjustments/{id}/post | Post adjustment (mutasi stok) |
| GET/POST | /api/v1/stock/transfers | List / create transfer |
| POST | /api/v1/stock/transfers/{id}/approve | Approve transfer |
| POST | /api/v1/stock/transfers/{id}/receive | Receive transfer |
| GET/POST | /api/v1/stock/opnames | List / create opname |
| POST | /api/v1/stock/opnames/{id}/post | Post opname (buat selisih adjustment) |
| GET | /api/v1/stock/movements | Stock movement (filter product/warehouse/date) |

## Sales
| Method | Endpoint |
|--------|----------|
| GET/POST | /api/v1/quotations |
| POST | /api/v1/quotations/{id}/submit |
| POST | /api/v1/quotations/{id}/approve |
| POST | /api/v1/quotations/{id}/reject |
| POST | /api/v1/quotations/{id}/convert |
| GET/POST | /api/v1/sales-orders |
| POST | /api/v1/sales-orders/{id}/post |
| POST | /api/v1/sales-orders/{id}/void |
| GET/POST | /api/v1/delivery-orders |
| POST | /api/v1/delivery-orders/{id}/ship |
| GET/POST | /api/v1/sales-invoices |
| POST | /api/v1/sales-invoices/{id}/post |

## Purchasing
| Method | Endpoint |
|--------|----------|
| GET/POST | /api/v1/purchase-orders |
| POST | /api/v1/purchase-orders/{id}/approve |
| POST | /api/v1/purchase-orders/{id}/reject |
| GET/POST | /api/v1/goods-receipts |
| POST | /api/v1/goods-receipts/{id}/post |
| GET/POST | /api/v1/purchase-invoices |
| POST | /api/v1/purchase-invoices/{id}/post |

## Payment
| Method | Endpoint |
|--------|----------|
| GET | /api/v1/payments?type=sales\|purchase |
| POST | /api/v1/payments (body: payable_type, payable_id, amount, method) |

## Reports
| Method | Endpoint |
|--------|----------|
| GET | /api/v1/reports/inventory |
| GET | /api/v1/reports/stock-card?product=&warehouse=&from=&to= |
| GET | /api/v1/reports/stock-movement |
| GET | /api/v1/reports/sales |
| GET | /api/v1/reports/purchase |
| GET | /api/v1/reports/invoice |
| GET | /api/v1/reports/profit |
| GET | /api/v1/reports/top-product |
| GET | /api/v1/reports/slow-moving |
| GET | /api/v1/reports/fast-moving |
| GET | /api/v1/reports/dead-stock |
| GET | /api/v1/reports/inventory-valuation |

## Contoh Request/Response

### POST /api/v1/customers
```json
{
  "code": "CST-0001",
  "name": "PT Maju Jaya",
  "npwp": "00.000.000.0-000.000",
  "address": "Jl. Merdeka 1",
  "phone": "081234567890",
  "email": "hello@majujaya.co.id",
  "pic_name": "Budi",
  "payment_term_days": 30,
  "credit_limit": 100000000,
  "is_active": true
}
```
Response `201`:
```json
{
  "data": {
    "id": "uuid...",
    "code": "CST-0001",
    "name": "PT Maju Jaya",
    ...
  }
}
```

### Error
```json
{ "message": "Validation failed.", "errors": { "name": ["The name field is required."] } }
```

## Status Code
- 200 OK, 201 Created, 204 No Content
- 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 422 Validation
