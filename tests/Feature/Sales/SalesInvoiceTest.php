<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\SalesInvoiceComponent;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouse;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesInvoiceTest extends TestCase
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

    private function makeDeliveredOrder(Product $product, float $orderQty = 10, float $deliverQty = 6): SalesOrder
    {
        $customer = Customer::create(['code' => 'CUS-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. Test', 'payment_term_days' => 14]);
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 100,
        ]);

        $order = SalesOrder::create([
            'number' => 'SO-202608-000001',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_APPROVED,
            'subtotal' => $orderQty * 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $orderQty * 50000,
            'created_by' => auth()->id(),
        ]);
        $soItem = $order->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => $orderQty,
            'unit_price' => 50000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => $orderQty * 50000,
        ]);

        $delivery = DeliveryOrder::create([
            'number' => 'DO-202608-000001',
            'sales_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'delivery_date' => now()->toDateString(),
            'status' => DeliveryOrder::STATUS_POSTED,
            'created_by' => auth()->id(),
        ]);
        $delivery->items()->create([
            'sales_order_item_id' => $soItem->id,
            'product_id' => $product->id,
            'qty' => $deliverQty,
        ]);

        return $order;
    }

    public function test_super_admin_can_view_invoice_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('sales.invoices.index'))
            ->assertOk()
            ->assertSee('Sales Invoice');
    }

    public function test_create_invoice_from_delivered_order(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeDeliveredOrder($product, 10, 6);

        Livewire::test(SalesInvoiceComponent::class)
            ->set('salesOrderId', $order->id)
            ->set('invoiceDate', now()->toDateString())
            ->set('dueDate', now()->addDays(14)->toDateString())
            ->call('create');

        $this->assertDatabaseHas('sales_invoices', [
            'sales_order_id' => $order->id,
            'status' => 'draft',
            'total' => 300000,
        ]);

        $this->assertDatabaseHas('sales_invoice_items', [
            'product_id' => $product->id,
            'qty' => 6,
            'line_total' => 300000,
        ]);
    }

    public function test_create_invoice_requires_delivered_items(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $customer = Customer::create(['code' => 'CUS-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. Test']);

        $order = SalesOrder::create([
            'number' => 'SO-202608-000002',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_APPROVED,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'created_by' => auth()->id(),
        ]);

        Livewire::test(SalesInvoiceComponent::class)
            ->set('salesOrderId', $order->id)
            ->set('items', [])
            ->call('create')
            ->assertHasErrors(['items' => 'required']);
    }

    public function test_post_invoice_then_payment_partial_and_paid(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeDeliveredOrder($product, 10, 6);

        $component = Livewire::test(SalesInvoiceComponent::class)
            ->set('salesOrderId', $order->id)
            ->set('invoiceDate', now()->toDateString())
            ->set('dueDate', now()->addDays(14)->toDateString())
            ->call('create');

        $invoice = SalesInvoice::first();

        $component->call('post', $invoice->id);
        $this->assertEquals(SalesInvoice::STATUS_POSTED, $invoice->fresh()->status);

        $component->call('openPayment', $invoice->id)
            ->set('paymentAmount', 100000)
            ->call('submitPayment');

        $invoice->refresh();
        $this->assertEquals(100000, (float) $invoice->paid_amount);
        $this->assertEquals(SalesInvoice::STATUS_PARTIAL, $invoice->status);

        $this->assertDatabaseHas('payments', [
            'payable_type' => SalesInvoice::class,
            'payable_id' => $invoice->id,
            'amount' => 100000,
        ]);

        $component->call('openPayment', $invoice->id)
            ->set('paymentAmount', 200000)
            ->call('submitPayment');

        $invoice->refresh();
        $this->assertEquals(300000, (float) $invoice->paid_amount);
        $this->assertEquals(SalesInvoice::STATUS_PAID, $invoice->status);
    }

    public function test_payment_over_balance_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeDeliveredOrder($product, 10, 6);

        Livewire::test(SalesInvoiceComponent::class)
            ->set('salesOrderId', $order->id)
            ->set('invoiceDate', now()->toDateString())
            ->set('dueDate', now()->addDays(14)->toDateString())
            ->call('create');

        $invoice = SalesInvoice::first();
        $invoice->update(['status' => SalesInvoice::STATUS_POSTED]);

        Livewire::test(SalesInvoiceComponent::class)
            ->call('openPayment', $invoice->id)
            ->set('paymentAmount', 999999)
            ->call('submitPayment');
    }
}
