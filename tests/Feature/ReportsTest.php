<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouse;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function seedStock(): void
    {
        $category = ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']);
        $unit = Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs']);
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);
        $product = Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
        ]);

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 25,
        ]);
    }

    public function test_reports_page_renders(): void
    {
        $this->actingAsAdmin();

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Laporan')
            ->assertSee('Stok per Gudang');
    }

    public function test_reports_page_requires_authentication(): void
    {
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_stock_on_hand_report_returns_rows(): void
    {
        $this->actingAsAdmin();
        $this->seedStock();

        $result = app(ReportService::class)->run('stock_on_hand');

        $this->assertEquals(['Produk', 'Nama', 'Gudang', 'Stok', 'Min', 'Reorder'], $result['headings']);
        $this->assertCount(1, $result['rows']);
        $this->assertContains('Kaos', $result['rows'][0]);
    }

    public function test_low_stock_report_filters_below_reorder_point(): void
    {
        $this->actingAsAdmin();

        $category = ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']);
        $unit = Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs']);
        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        $product = Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos Menipis',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
            'reorder_point' => 50,
        ]);

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty_on_hand' => 10,
        ]);

        $result = app(ReportService::class)->run('low_stock');

        $this->assertCount(1, $result['rows']);
        $this->assertContains('Kaos Menipis', $result['rows'][0]);
    }

    public function test_all_report_definitions_are_runable(): void
    {
        $this->actingAsAdmin();
        $this->seedStock();

        $svc = app(ReportService::class);

        foreach (array_keys($svc->definitions()) as $key) {
            $result = $svc->run($key);
            $this->assertArrayHasKey('title', $result);
            $this->assertArrayHasKey('headings', $result);
            $this->assertArrayHasKey('rows', $result);
        }

        $this->assertCount(15, $svc->definitions());
    }

    public function test_unknown_report_throws(): void
    {
        $this->actingAsAdmin();

        $this->expectException(\InvalidArgumentException::class);

        app(ReportService::class)->run('does_not_exist');
    }

    public function test_excel_export_downloads_file(): void
    {
        $this->actingAsAdmin();
        $this->seedStock();

        $response = \Livewire\Livewire::test(\App\Livewire\Reports::class)
            ->set('report', 'stock_on_hand')
            ->call('exportExcel');

        $response->assertOk();
    }
}
