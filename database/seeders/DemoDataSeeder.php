<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductWarehouse;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin System', 'password' => bcrypt('password'), 'email_verified_at' => now(), 'is_active' => true],
        );

        // --- Master data ---
        $units = collect([
            ['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs'],
            ['code' => 'BOX', 'name' => 'Box', 'symbol' => 'box'],
            ['code' => 'LTR', 'name' => 'Liter', 'symbol' => 'L'],
            ['code' => 'KG', 'name' => 'Kilogram', 'symbol' => 'kg'],
        ])->map(fn ($u) => Unit::firstOrCreate(['code' => $u['code']], $u));

        $categories = collect([
            ['code' => 'ELK', 'name' => 'Elektronik'],
            ['code' => 'FTM', 'name' => 'Fashion & Tekstil'],
            ['code' => 'ATK', 'name' => 'Alat Tulis Kantor'],
            ['code' => 'MKN', 'name' => 'Makanan & Minuman'],
        ])->map(fn ($c) => ProductCategory::firstOrCreate(['code' => $c['code']], $c));

        $brands = collect([
            ['code' => 'SNY', 'name' => 'Sony'],
            ['code' => 'ADB', 'name' => 'Adidas'],
            ['code' => 'PLL', 'name' => 'Pilot'],
            ['code' => 'INDF', 'name' => 'Indofood'],
        ])->map(fn ($b) => ProductBrand::firstOrCreate(['code' => $b['code']], $b));

        $warehouses = collect([
            ['code' => 'WH-MAIN', 'name' => 'Gudang Utama', 'address' => 'Jl. Raya No. 1'],
            ['code' => 'WH-SAT', 'name' => 'Gudang Satelit', 'address' => 'Kawasan Industri Blok B'],
        ])->map(fn ($w) => Warehouse::firstOrCreate(['code' => $w['code']], $w));

        $tax11 = Tax::firstOrCreate(['code' => 'PPN'], ['name' => 'PPN', 'rate' => 11]);

        $customers = collect([
            ['code' => 'CUS-001', 'name' => 'PT Maju Jaya', 'phone' => '0812-1111', 'payment_term_days' => 14, 'credit_limit' => 100000000],
            ['code' => 'CUS-002', 'name' => 'CV Sinar Abadi', 'phone' => '0812-2222', 'payment_term_days' => 30, 'credit_limit' => 50000000],
            ['code' => 'CUS-003', 'name' => 'Toko Berkah', 'phone' => '0812-3333', 'payment_term_days' => 7, 'credit_limit' => 20000000],
        ])->map(fn ($c) => Customer::firstOrCreate(['code' => $c['code']], $c + ['address' => 'Jl. Contoh No. '.substr($c['code'], -1), 'is_active' => true]));

        $suppliers = collect([
            ['code' => 'SUP-001', 'name' => 'PT Sumber Rejeki', 'phone' => '021-1111', 'payment_term_days' => 30, 'credit_limit' => 200000000],
            ['code' => 'SUP-002', 'name' => 'CV Distributor Nusantara', 'phone' => '021-2222', 'payment_term_days' => 14, 'credit_limit' => 150000000],
        ])->map(fn ($s) => Supplier::firstOrCreate(['code' => $s['code']], $s + ['address' => 'Jl. Industri No. '.substr($s['code'], -1), 'is_active' => true]));

        // --- Products ---
        $products = [
            ['code' => 'PRD-0001', 'name' => 'Headphone Wireless', 'category' => 'ELK', 'brand' => 'SNY', 'unit' => 'PCS', 'selling' => 750000, 'purchase' => 500000, 'min' => 5, 'reorder' => 10],
            ['code' => 'PRD-0002', 'name' => 'Speaker Bluetooth', 'category' => 'ELK', 'brand' => 'SNY', 'unit' => 'PCS', 'selling' => 450000, 'purchase' => 300000, 'min' => 5, 'reorder' => 8],
            ['code' => 'PRD-0003', 'name' => 'Kaos Polos', 'category' => 'FTM', 'brand' => 'ADB', 'unit' => 'PCS', 'selling' => 120000, 'purchase' => 60000, 'min' => 20, 'reorder' => 30],
            ['code' => 'PRD-0004', 'name' => 'Jaket Hoodie', 'category' => 'FTM', 'brand' => 'ADB', 'unit' => 'PCS', 'selling' => 250000, 'purchase' => 140000, 'min' => 10, 'reorder' => 15],
            ['code' => 'PRD-0005', 'name' => 'Pulpen Gel 0.5mm', 'category' => 'ATK', 'brand' => 'PLL', 'unit' => 'BOX', 'selling' => 45000, 'purchase' => 25000, 'min' => 20, 'reorder' => 40],
            ['code' => 'PRD-0006', 'name' => 'Buku Tulis 38 Lembar', 'category' => 'ATK', 'brand' => 'PLL', 'unit' => 'PCS', 'selling' => 8000, 'purchase' => 4000, 'min' => 50, 'reorder' => 100],
            ['code' => 'PRD-0007', 'name' => 'Mie Instan Goreng', 'category' => 'MKN', 'brand' => 'INDF', 'unit' => 'BOX', 'selling' => 100000, 'purchase' => 82000, 'min' => 10, 'reorder' => 15],
            ['code' => 'PRD-0008', 'name' => 'Bumbu Nasi Goreng', 'category' => 'MKN', 'brand' => 'INDF', 'unit' => 'KG', 'selling' => 35000, 'purchase' => 20000, 'min' => 15, 'reorder' => 20],
        ];

        $productModels = [];
        foreach ($products as $p) {
            $product = Product::firstOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'category_id' => $categories->firstWhere('code', $p['category'])->id,
                    'brand_id' => $brands->firstWhere('code', $p['brand'])->id,
                    'unit_id' => $units->firstWhere('code', $p['unit'])->id,
                    'selling_price' => $p['selling'],
                    'purchase_price' => $p['purchase'],
                    'min_stock' => $p['min'],
                    'reorder_point' => $p['reorder'],
                    'is_active' => true,
                ],
            );
            $productModels[] = $product;

            ProductWarehouse::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouses[0]->id],
                ['qty_on_hand' => $p['reorder'] * 2 + 5],
            );
            ProductWarehouse::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouses[1]->id],
                ['qty_on_hand' => $p['reorder']],
            );
        }

        $so = SalesOrder::firstOrCreate(
            ['number' => 'SO-'.date('Ymd').'-000001'],
            [
                'customer_id' => $customers[0]->id,
                'order_date' => now()->subDays(20)->toDateString(),
                'status' => SalesOrder::STATUS_APPROVED,
                'subtotal' => 1250000,
                'discount_amount' => 0,
                'tax_amount' => 137500,
                'total' => 1387500,
                'notes' => 'Pesanan demo',
                'created_by' => $admin->id,
            ],
        );
        $so->items()->firstOrCreate(
            ['product_id' => $productModels[0]->id],
            ['description' => 'Headphone Wireless', 'qty' => 1, 'unit_price' => 750000, 'discount' => 0, 'tax_id' => $tax11->id, 'line_total' => 750000],
        );
        $so->items()->firstOrCreate(
            ['product_id' => $productModels[3]->id],
            ['description' => 'Jaket Hoodie', 'qty' => 2, 'unit_price' => 250000, 'discount' => 0, 'tax_id' => $tax11->id, 'line_total' => 500000],
        );

        $delivery = DeliveryOrder::firstOrCreate(
            ['number' => 'DO-'.date('Ymd').'-000001'],
            [
                'sales_order_id' => $so->id,
                'warehouse_id' => $warehouses[0]->id,
                'delivery_date' => now()->subDays(18)->toDateString(),
                'status' => DeliveryOrder::STATUS_POSTED,
                'notes' => 'Dikirim via kurir',
                'created_by' => $admin->id,
            ],
        );
        $delivery->items()->firstOrCreate(
            ['sales_order_item_id' => $so->items()->first()->id],
            ['product_id' => $productModels[0]->id, 'qty' => 1],
        );
        $delivery->items()->firstOrCreate(
            ['sales_order_item_id' => $so->items()->get()[1]->id],
            ['product_id' => $productModels[3]->id, 'qty' => 2],
        );

        $invoice = SalesInvoice::firstOrCreate(
            ['number' => 'INV-'.date('Ymd').'-000001'],
            [
                'sales_order_id' => $so->id,
                'customer_id' => $customers[0]->id,
                'invoice_date' => now()->subDays(18)->toDateString(),
                'due_date' => now()->subDays(4)->toDateString(),
                'status' => SalesInvoice::STATUS_PARTIAL,
                'subtotal' => 1250000,
                'discount_amount' => 0,
                'tax_amount' => 137500,
                'total' => 1387500,
                'paid_amount' => 500000,
                'created_by' => $admin->id,
            ],
        );
        $invoice->items()->firstOrCreate(
            ['product_id' => $productModels[0]->id],
            ['description' => 'Headphone Wireless', 'qty' => 1, 'unit_price' => 750000, 'discount' => 0, 'tax_id' => $tax11->id, 'line_total' => 750000],
        );
        $invoice->items()->firstOrCreate(
            ['product_id' => $productModels[3]->id],
            ['description' => 'Jaket Hoodie', 'qty' => 2, 'unit_price' => 250000, 'discount' => 0, 'tax_id' => $tax11->id, 'line_total' => 500000],
        );

        $invoice->payments()->firstOrCreate(
            ['number' => 'PAY-'.date('Ymd').'-000001'],
            [
                'payment_date' => now()->subDays(15)->toDateString(),
                'payment_method' => 'bank_transfer',
                'reference' => 'BNI-123456',
                'amount' => 500000,
                'status' => 'posted',
                'created_by' => $admin->id,
            ],
        );

        $po = PurchaseOrder::firstOrCreate(
            ['number' => 'PO-'.date('Ymd').'-000001'],
            [
                'supplier_id' => $suppliers[0]->id,
                'order_date' => now()->subDays(10)->toDateString(),
                'status' => PurchaseOrder::STATUS_APPROVED,
                'subtotal' => 2050000,
                'discount_amount' => 0,
                'tax_amount' => 225500,
                'total' => 2275500,
                'created_by' => $admin->id,
            ],
        );
        $po->items()->firstOrCreate(
            ['product_id' => $productModels[0]->id],
            ['description' => 'Headphone Wireless', 'qty' => 3, 'unit_price' => 500000, 'discount' => 0, 'tax_id' => $tax11->id, 'line_total' => 1500000],
        );
        $po->items()->firstOrCreate(
            ['product_id' => $productModels[1]->id],
            ['description' => 'Speaker Bluetooth', 'qty' => 2, 'unit_price' => 275000, 'discount' => 0, 'tax_id' => $tax11->id, 'line_total' => 550000],
        );

        $purchaseInvoice = PurchaseInvoice::firstOrCreate(
            ['number' => 'PINV-'.date('Ymd').'-000001'],
            [
                'supplier_id' => $suppliers[0]->id,
                'purchase_order_id' => $po->id,
                'invoice_date' => now()->subDays(8)->toDateString(),
                'due_date' => now()->addDays(6)->toDateString(),
                'status' => PurchaseInvoice::STATUS_PARTIAL,
                'subtotal' => 2050000,
                'discount_amount' => 0,
                'tax_amount' => 225500,
                'total' => 2275500,
                'paid_amount' => 1000000,
                'created_by' => $admin->id,
            ],
        );
        $purchaseInvoice->items()->firstOrCreate(
            ['product_id' => $productModels[0]->id],
            ['description' => 'Headphone Wireless', 'qty' => 3, 'unit_price' => 500000, 'discount' => 0, 'tax_id' => $tax11->id, 'line_total' => 1500000],
        );
        $purchaseInvoice->items()->firstOrCreate(
            ['product_id' => $productModels[1]->id],
            ['description' => 'Speaker Bluetooth', 'qty' => 2, 'unit_price' => 275000, 'discount' => 0, 'tax_id' => $tax11->id, 'line_total' => 550000],
        );

        $purchaseInvoice->payments()->firstOrCreate(
            ['number' => 'PAY-'.date('Ymd').'-000002'],
            [
                'payment_date' => now()->subDays(6)->toDateString(),
                'payment_method' => 'bank_transfer',
                'reference' => 'BCA-654321',
                'amount' => 1000000,
                'status' => 'posted',
                'created_by' => $admin->id,
            ],
        );
    }
}
