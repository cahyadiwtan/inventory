<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Inventory\StockOpnameComponent;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouse;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOpnameTest extends TestCase
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

    public function test_super_admin_can_view_opname_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('inventory.opnames.index'))
            ->assertOk()
            ->assertSee('Stock Opname');
    }

    public function test_load_products_preloads_system_quantity(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 120,
        ]);

        Livewire::test(StockOpnameComponent::class)
            ->set('warehouseId', $warehouse->id)
            ->call('loadProducts')
            ->assertSet('items.0.system_qty', '120.00');
    }

    public function test_submit_creates_opname_with_difference(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 120,
        ]);

        Livewire::test(StockOpnameComponent::class)
            ->set('warehouseId', $warehouse->id)
            ->set('opnameDate', '2026-08-06')
            ->set('items', [[
                'product_id' => $product->id,
                'product_name' => 'Kaos',
                'system_qty' => 120,
                'actual_qty' => 118,
            ]])
            ->call('submit');

        $opname = StockOpname::first();

        $this->assertNotNull($opname);
        $this->assertEquals(StockOpname::STATUS_SUBMITTED, $opname->status);

        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'product_id' => $product->id,
            'system_qty' => 120,
            'actual_qty' => 118,
            'difference' => -2,
        ]);
    }

    public function test_approve_then_post_applies_difference(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 120,
        ]);

        $opname = StockOpname::create([
            'number' => 'OPN-202608-000001',
            'warehouse_id' => $warehouse->id,
            'opname_date' => '2026-08-06',
            'status' => StockOpname::STATUS_SUBMITTED,
            'created_by' => auth()->id(),
        ]);
        $opname->items()->create([
            'product_id' => $product->id,
            'system_qty' => 120,
            'actual_qty' => 118,
            'difference' => -2,
        ]);

        $component = Livewire::test(StockOpnameComponent::class);
        $component->call('approve', $opname->id);
        $component->call('post', $opname->id);

        $this->assertEquals(118, (float) ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)->value('qty_on_hand'));

        $opname->refresh();
        $this->assertEquals(StockOpname::STATUS_POSTED, $opname->status);
    }

    public function test_opname_date_is_required(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        Livewire::test(StockOpnameComponent::class)
            ->set('warehouseId', $warehouse->id)
            ->set('opnameDate', '')
            ->set('items', [[
                'product_id' => $product->id,
                'product_name' => 'Kaos',
                'system_qty' => 120,
                'actual_qty' => 118,
            ]])
            ->call('submit')
            ->assertHasErrors(['opnameDate' => 'required']);
    }
}
