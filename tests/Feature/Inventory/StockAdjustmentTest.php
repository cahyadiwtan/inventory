<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Inventory\StockAdjustment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouse;
use App\Models\StockAdjustment as StockAdjustmentModel;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
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
            'min_stock' => 0,
            'max_stock' => 0,
            'reorder_point' => 0,
        ]);
    }

    public function test_super_admin_can_view_adjustment_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('inventory.adjustments.index'))
            ->assertOk()
            ->assertSee('Stock Adjustment');
    }

    public function test_plus_adjustment_posts_stock_in(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        Livewire::test(StockAdjustment::class)
            ->set('type', 'plus')
            ->set('warehouseId', $warehouse->id)
            ->set('reason', 'Koreksi stok')
            ->set('items', [['product_id' => $product->id, 'qty' => 50]])
            ->call('post');

        $this->assertDatabaseHas('stock_adjustments', [
            'warehouse_id' => $warehouse->id,
            'type' => 'plus',
            'reason' => 'Koreksi stok',
            'status' => 'posted',
        ]);
        $this->assertDatabaseHas('product_warehouses', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 50,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'in',
            'qty' => 50,
        ]);
    }

    public function test_minus_adjustment_posts_stock_out(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 100,
        ]);

        Livewire::test(StockAdjustment::class)
            ->set('type', 'minus')
            ->set('warehouseId', $warehouse->id)
            ->set('reason', 'Barang rusak')
            ->set('items', [['product_id' => $product->id, 'qty' => 20]])
            ->call('post');

        $this->assertDatabaseHas('product_warehouses', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 80,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'out',
            'qty' => 20,
        ]);
    }

    public function test_reason_is_required(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        Livewire::test(StockAdjustment::class)
            ->set('type', 'plus')
            ->set('warehouseId', $warehouse->id)
            ->set('reason', '')
            ->set('items', [['product_id' => $product->id, 'qty' => 50]])
            ->call('post')
            ->assertHasErrors(['reason' => 'required']);
    }

    public function test_items_are_required(): void
    {
        $this->actingAsAdmin();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        Livewire::test(StockAdjustment::class)
            ->set('type', 'plus')
            ->set('warehouseId', $warehouse->id)
            ->set('reason', 'Koreksi')
            ->set('items', [])
            ->call('post')
            ->assertHasErrors(['items' => 'required']);
    }

    public function test_minus_adjustment_throws_when_insufficient_stock(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        Livewire::test(StockAdjustment::class)
            ->set('type', 'minus')
            ->set('warehouseId', $warehouse->id)
            ->set('reason', 'Barang hilang')
            ->set('items', [['product_id' => $product->id, 'qty' => 20]])
            ->call('post');
    }
}
