<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\DeliveryOrderComponent;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouse;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryOrderTest extends TestCase
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

    private function makeApprovedOrder(Product $product, float $qty = 10): SalesOrder
    {
        $customer = Customer::create(['code' => 'CUS-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. Test']);

        $order = SalesOrder::create([
            'number' => 'SO-202608-000001',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_APPROVED,
            'subtotal' => $qty * 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $qty * 50000,
            'created_by' => auth()->id(),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => $qty,
            'unit_price' => 50000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => $qty * 50000,
        ]);

        return $order;
    }

    private function makeWarehouse(): Warehouse
    {
        return Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);
    }

    public function test_super_admin_can_view_delivery_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('sales.deliveries.index'))
            ->assertOk()
            ->assertSee('Delivery Order');
    }

    public function test_selecting_order_loads_remaining_items(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeApprovedOrder($product, 10);
        $this->makeWarehouse();

        Livewire::test(DeliveryOrderComponent::class)
            ->set('salesOrderId', $order->id)
            ->assertSet('items.0.qty', 10)
            ->assertSet('items.0.product_id', $product->id);
    }

    public function test_post_delivery_moves_stock_out(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeApprovedOrder($product, 10);
        $warehouse = $this->makeWarehouse();

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 100,
        ]);

        $component = Livewire::test(DeliveryOrderComponent::class)
            ->set('salesOrderId', $order->id)
            ->set('warehouseId', $warehouse->id)
            ->set('deliveryDate', now()->toDateString())
            ->call('create');

        $delivery = DeliveryOrder::first();

        $component->call('post', $delivery->id);

        $this->assertEquals(90, (float) ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)->value('qty_on_hand'));

        $this->assertEquals(DeliveryOrder::STATUS_POSTED, $delivery->fresh()->status);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => 'out',
            'reference_type' => DeliveryOrder::class,
        ]);
    }

    public function test_post_over_remaining_quantity_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeApprovedOrder($product, 10);
        $warehouse = $this->makeWarehouse();

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 100,
        ]);

        Livewire::test(DeliveryOrderComponent::class)
            ->set('salesOrderId', $order->id)
            ->set('warehouseId', $warehouse->id)
            ->call('create');

        $delivery = DeliveryOrder::first();
        $delivery->items()->update(['qty' => 20]);

        Livewire::test(DeliveryOrderComponent::class)
            ->call('post', $delivery->id);
    }

    public function test_post_throws_when_stock_insufficient(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeApprovedOrder($product, 10);
        $warehouse = $this->makeWarehouse();

        Livewire::test(DeliveryOrderComponent::class)
            ->set('salesOrderId', $order->id)
            ->set('warehouseId', $warehouse->id)
            ->call('create');

        $delivery = DeliveryOrder::first();

        Livewire::test(DeliveryOrderComponent::class)
            ->call('post', $delivery->id);
    }
}
