<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $this->actingAsAdmin();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Penjualan 6 Bulan Terakhir')
            ->assertSee('Aktivitas Terbaru');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_low_stock_products(): void
    {
        $this->actingAsAdmin();

        $category = ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']);
        $unit = Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs']);
        Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos Habis',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
            'reorder_point' => 10,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kaos Habis');
    }

    public function test_dashboard_totals_reflect_sales_orders(): void
    {
        $this->actingAsAdmin();

        $category = ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']);
        $unit = Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs']);
        $product = Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
        ]);

        $customer = Customer::create(['code' => 'CUS-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. Test', 'payment_term_days' => 14]);

        SalesOrder::create([
            'number' => 'SO-202608-000001',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_APPROVED,
            'subtotal' => 500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 500000,
            'created_by' => auth()->id(),
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('500,000');
    }
}
