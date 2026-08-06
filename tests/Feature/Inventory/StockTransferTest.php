<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Inventory\StockTransferComponent;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouse;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockTransferTest extends TestCase
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

    private function makeWarehouses(): array
    {
        return [
            Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']),
            Warehouse::create(['code' => 'WH-02', 'name' => 'Gudang B', 'address' => 'Jl. B']),
        ];
    }

    public function test_super_admin_can_view_transfer_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('inventory.transfers.index'))
            ->assertOk()
            ->assertSee('Stock Transfer');
    }

    public function test_request_creates_transfer(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        [$from, $to] = $this->makeWarehouses();

        Livewire::test(StockTransferComponent::class)
            ->set('fromWarehouseId', $from->id)
            ->set('toWarehouseId', $to->id)
            ->set('notes', 'Rutin')
            ->set('items', [['product_id' => $product->id, 'qty' => 30]])
            ->call('request');

        $this->assertDatabaseHas('stock_transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => 'requested',
            'notes' => 'Rutin',
        ]);
    }

    public function test_approve_then_transfer_then_receive_moves_stock(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        [$from, $to] = $this->makeWarehouses();

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $from->id,
            'qty_on_hand' => 100,
        ]);

        $transfer = StockTransfer::create([
            'number' => 'TRF-202608-000001',
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => StockTransfer::STATUS_REQUESTED,
            'created_by' => auth()->id(),
        ]);
        $transfer->items()->create(['product_id' => $product->id, 'qty' => 40]);

        $component = Livewire::test(StockTransferComponent::class);
        $component->call('approve', $transfer->id);
        $component->call('transfer', $transfer->id);
        $component->call('receive', $transfer->id);

        $this->assertEquals(60, (float) ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $from->id)->value('qty_on_hand'));
        $this->assertEquals(40, (float) ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $to->id)->value('qty_on_hand'));

        $transfer->refresh();
        $this->assertEquals(StockTransfer::STATUS_RECEIVED, $transfer->status);
    }

    public function test_reject_sets_status(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        [$from, $to] = $this->makeWarehouses();

        $transfer = StockTransfer::create([
            'number' => 'TRF-202608-000001',
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => StockTransfer::STATUS_REQUESTED,
            'created_by' => auth()->id(),
        ]);
        $transfer->items()->create(['product_id' => $product->id, 'qty' => 40]);

        Livewire::test(StockTransferComponent::class)
            ->call('reject', $transfer->id);

        $transfer->refresh();
        $this->assertEquals(StockTransfer::STATUS_REJECTED, $transfer->status);
    }

    public function test_from_and_to_warehouse_cannot_be_same(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();
        [$from] = $this->makeWarehouses();

        Livewire::test(StockTransferComponent::class)
            ->set('fromWarehouseId', $from->id)
            ->set('toWarehouseId', $from->id)
            ->set('items', [['product_id' => $product->id, 'qty' => 30]])
            ->call('request')
            ->assertHasErrors(['fromWarehouseId' => 'different']);
    }

    public function test_transfer_throws_when_source_stock_insufficient(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->actingAsAdmin();
        $product = $this->makeProduct();
        [$from, $to] = $this->makeWarehouses();

        $transfer = StockTransfer::create([
            'number' => 'TRF-202608-000001',
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => StockTransfer::STATUS_APPROVED,
            'created_by' => auth()->id(),
        ]);
        $transfer->items()->create(['product_id' => $product->id, 'qty' => 40]);

        Livewire::test(StockTransferComponent::class)
            ->call('transfer', $transfer->id);
    }
}
