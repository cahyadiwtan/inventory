<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouse;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

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

    private function makeWarehouse(string $code = 'WH-01'): Warehouse
    {
        return Warehouse::create(['code' => $code, 'name' => $code.' Warehouse', 'address' => 'Jl. A']);
    }

    public function test_move_in_creates_ledger_and_updates_quantity(): void
    {
        $product = $this->makeProduct();
        $warehouse = $this->makeWarehouse();

        $movement = app(StockService::class)->move($product->id, $warehouse->id, 'in', 100);

        $this->assertInstanceOf(StockMovement::class, $movement);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => 'in',
            'qty' => 100,
            'qty_before' => 0,
            'qty_after' => 100,
        ]);
        $this->assertDatabaseHas('product_warehouses', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 100,
        ]);
    }

    public function test_move_out_decrements_quantity(): void
    {
        $product = $this->makeProduct();
        $warehouse = $this->makeWarehouse();

        $service = app(StockService::class);
        $service->move($product->id, $warehouse->id, 'in', 100);
        $service->move($product->id, $warehouse->id, 'out', 30);

        $this->assertEquals(70, (float) ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->value('qty_on_hand'));
    }

    public function test_move_out_with_insufficient_stock_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        $product = $this->makeProduct();
        $warehouse = $this->makeWarehouse();

        app(StockService::class)->move($product->id, $warehouse->id, 'out', 10);
    }

    public function test_transfer_moves_stock_between_warehouses(): void
    {
        $product = $this->makeProduct();
        $from = $this->makeWarehouse('WH-01');
        $to = $this->makeWarehouse('WH-02');

        $service = app(StockService::class);
        $service->move($product->id, $from->id, 'in', 100);
        $service->transfer($product->id, $from->id, $to->id, 40);

        $this->assertEquals(60, (float) ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $from->id)->value('qty_on_hand'));
        $this->assertEquals(40, (float) ProductWarehouse::where('product_id', $product->id)
            ->where('warehouse_id', $to->id)->value('qty_on_hand'));
        $this->assertEquals(3, StockMovement::where('product_id', $product->id)->count());
    }

    public function test_invalid_movement_type_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $product = $this->makeProduct();
        $warehouse = $this->makeWarehouse();

        app(StockService::class)->move($product->id, $warehouse->id, 'invalid', 10);
    }
}
