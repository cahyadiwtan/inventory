<?php

namespace Tests\Feature\Purchasing;

use App\Livewire\Purchasing\PurchaseOrderComponent;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeSupplier(): Supplier
    {
        return Supplier::create([
            'code' => 'SUP-01',
            'name' => 'PT Pemasok Utama',
            'address' => 'Jl. Test',
            'payment_term_days' => 30,
        ]);
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

    private function makeOrder(string $status = PurchaseOrder::STATUS_DRAFT): PurchaseOrder
    {
        $supplier = $this->makeSupplier();
        $product = $this->makeProduct();

        $order = PurchaseOrder::create([
            'number' => 'PO-202608-000001',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(7)->toDateString(),
            'status' => $status,
            'subtotal' => 60000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 60000,
            'created_by' => auth()->id(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => 2,
            'unit_price' => 30000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => 60000,
        ]);

        return $order;
    }

    public function test_super_admin_can_view_purchase_order_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('purchasing.orders.index'))
            ->assertOk()
            ->assertSee('Purchase Order');
    }

    public function test_create_purchase_order_stores_document_and_totals(): void
    {
        $this->actingAsAdmin();
        $supplier = $this->makeSupplier();
        $product = $this->makeProduct();

        Livewire::test(PurchaseOrderComponent::class)
            ->set('supplierId', $supplier->id)
            ->set('orderDate', now()->toDateString())
            ->set('items', [[
                'product_id' => $product->id,
                'qty' => 2,
                'unit_price' => 30000,
                'discount' => 0,
                'tax_id' => null,
            ]])
            ->call('save');

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'subtotal' => 60000,
            'total' => 60000,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $product->id,
            'qty' => 2,
            'line_total' => 60000,
        ]);
    }

    public function test_approve_sets_order_approved(): void
    {
        $this->actingAsAdmin();
        $order = $this->makeOrder();

        Livewire::test(PurchaseOrderComponent::class)->call('approve', $order->id);

        $this->assertEquals(PurchaseOrder::STATUS_APPROVED, $order->fresh()->status);
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => PurchaseOrder::class,
            'approvable_id' => $order->id,
            'action' => 'approve',
        ]);
    }

    public function test_reject_sets_order_rejected(): void
    {
        $this->actingAsAdmin();
        $order = $this->makeOrder();

        Livewire::test(PurchaseOrderComponent::class)->call('reject', $order->id);

        $this->assertEquals(PurchaseOrder::STATUS_REJECTED, $order->fresh()->status);
    }

    public function test_cancel_approved_order(): void
    {
        $this->actingAsAdmin();
        $order = $this->makeOrder(PurchaseOrder::STATUS_APPROVED);

        Livewire::test(PurchaseOrderComponent::class)->call('cancel', $order->id);

        $this->assertEquals(PurchaseOrder::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_validation_requires_supplier_and_items(): void
    {
        $this->actingAsAdmin();

        Livewire::test(PurchaseOrderComponent::class)
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['supplierId' => 'required', 'items' => 'required']);
    }
}
