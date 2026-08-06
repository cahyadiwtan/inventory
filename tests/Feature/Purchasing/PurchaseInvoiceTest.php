<?php

namespace Tests\Feature\Purchasing;

use App\Livewire\Purchasing\PurchaseInvoiceComponent;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeProduct(): Product
    {
        return Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel'])->id,
            'unit_id' => Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs'])->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
        ]);
    }

    private function makeReceivedOrder(Product $product, float $orderQty = 10, float $receiveQty = 6): PurchaseOrder
    {
        $supplier = Supplier::create(['code' => 'SUP-01', 'name' => 'PT Pemasok Utama', 'address' => 'Jl. Test', 'payment_term_days' => 30]);
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        $order = PurchaseOrder::create([
            'number' => 'PO-202608-000001',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
            'subtotal' => $orderQty * 30000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $orderQty * 30000,
            'created_by' => auth()->id(),
        ]);
        $poItem = $order->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => $orderQty,
            'unit_price' => 30000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => $orderQty * 30000,
        ]);

        $receipt = GoodsReceipt::create([
            'number' => 'GRN-202608-000001',
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'receipt_date' => now()->toDateString(),
            'status' => GoodsReceipt::STATUS_POSTED,
            'created_by' => auth()->id(),
        ]);
        $receipt->items()->create([
            'purchase_order_item_id' => $poItem->id,
            'product_id' => $product->id,
            'qty' => $receiveQty,
        ]);

        return $order;
    }

    public function test_super_admin_can_view_purchase_invoice_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('purchasing.invoices.index'))
            ->assertOk()
            ->assertSee('Purchase Invoice');
    }

    public function test_create_invoice_from_received_order(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeReceivedOrder($product, 10, 6);

        Livewire::test(PurchaseInvoiceComponent::class)
            ->set('purchaseOrderId', $order->id)
            ->set('invoiceDate', now()->toDateString())
            ->set('dueDate', now()->addDays(30)->toDateString())
            ->call('create');

        $this->assertDatabaseHas('purchase_invoices', [
            'purchase_order_id' => $order->id,
            'status' => 'draft',
            'total' => 180000,
        ]);

        $this->assertDatabaseHas('purchase_invoice_items', [
            'product_id' => $product->id,
            'qty' => 6,
            'line_total' => 180000,
        ]);
    }

    public function test_post_invoice_then_payment_partial_and_paid(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeReceivedOrder($product, 10, 6);

        $component = Livewire::test(PurchaseInvoiceComponent::class)
            ->set('purchaseOrderId', $order->id)
            ->set('invoiceDate', now()->toDateString())
            ->set('dueDate', now()->addDays(30)->toDateString())
            ->call('create');

        $invoice = PurchaseInvoice::first();

        $component->call('post', $invoice->id);
        $this->assertEquals(PurchaseInvoice::STATUS_POSTED, $invoice->fresh()->status);

        $component->call('openPayment', $invoice->id)
            ->set('paymentAmount', 100000)
            ->call('submitPayment');

        $invoice->refresh();
        $this->assertEquals(100000, (float) $invoice->paid_amount);
        $this->assertEquals(PurchaseInvoice::STATUS_PARTIAL, $invoice->status);

        $this->assertDatabaseHas('payments', [
            'payable_type' => PurchaseInvoice::class,
            'payable_id' => $invoice->id,
            'amount' => 100000,
        ]);

        $component->call('openPayment', $invoice->id)
            ->set('paymentAmount', 80000)
            ->call('submitPayment');

        $invoice->refresh();
        $this->assertEquals(180000, (float) $invoice->paid_amount);
        $this->assertEquals(PurchaseInvoice::STATUS_PAID, $invoice->status);
    }

    public function test_payment_over_balance_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeReceivedOrder($product, 10, 6);

        Livewire::test(PurchaseInvoiceComponent::class)
            ->set('purchaseOrderId', $order->id)
            ->set('invoiceDate', now()->toDateString())
            ->set('dueDate', now()->addDays(30)->toDateString())
            ->call('create');

        $invoice = PurchaseInvoice::first();
        $invoice->update(['status' => PurchaseInvoice::STATUS_POSTED]);

        Livewire::test(PurchaseInvoiceComponent::class)
            ->call('openPayment', $invoice->id)
            ->set('paymentAmount', 999999)
            ->call('submitPayment');
    }
}
