<?php

namespace Tests\Feature\Purchasing;

use App\Livewire\Purchasing\GoodsReceiptComponent;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
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

    private function makeApprovedOrder(Product $product, float $qty = 10): PurchaseOrder
    {
        $supplier = Supplier::create(['code' => 'SUP-01', 'name' => 'PT Pemasok Utama', 'address' => 'Jl. Test']);

        $order = PurchaseOrder::create([
            'number' => 'PO-202608-000001',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_APPROVED,
            'subtotal' => $qty * 30000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $qty * 30000,
            'created_by' => auth()->id(),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => $qty,
            'unit_price' => 30000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => $qty * 30000,
        ]);

        return $order;
    }

    private function makeWarehouse(): Warehouse
    {
        return Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);
    }

    public function test_super_admin_can_view_goods_receipt_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('purchasing.receipts.index'))
            ->assertOk()
            ->assertSee('Goods Receipt');
    }

    public function test_selecting_order_loads_remaining_items(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeApprovedOrder($product, 10);
        $this->makeWarehouse();

        Livewire::test(GoodsReceiptComponent::class)
            ->set('purchaseOrderId', $order->id)
            ->assertSet('items.0.qty', 10)
            ->assertSet('items.0.product_id', $product->id);
    }

    public function test_post_receipt_moves_stock_in_and_marks_order_received(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeApprovedOrder($product, 10);
        $warehouse = $this->makeWarehouse();

        $component = Livewire::test(GoodsReceiptComponent::class)
            ->set('purchaseOrderId', $order->id)
            ->set('warehouseId', $warehouse->id)
            ->set('receiptDate', now()->toDateString())
            ->call('create');

        $receipt = GoodsReceipt::first();

        $component->call('post', $receipt->id);

        $this->assertEquals(10, (float) \App\Models\ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)->value('qty_on_hand'));

        $this->assertEquals(GoodsReceipt::STATUS_POSTED, $receipt->fresh()->status);
        $this->assertEquals(PurchaseOrder::STATUS_RECEIVED, $order->fresh()->status);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => 'in',
            'reference_type' => GoodsReceipt::class,
        ]);
    }

    public function test_post_over_remaining_quantity_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $order = $this->makeApprovedOrder($product, 10);
        $warehouse = $this->makeWarehouse();

        Livewire::test(GoodsReceiptComponent::class)
            ->set('purchaseOrderId', $order->id)
            ->set('warehouseId', $warehouse->id)
            ->call('create');

        $receipt = GoodsReceipt::first();
        $receipt->items()->update(['qty' => 20]);

        Livewire::test(GoodsReceiptComponent::class)
            ->call('post', $receipt->id);
    }
}
