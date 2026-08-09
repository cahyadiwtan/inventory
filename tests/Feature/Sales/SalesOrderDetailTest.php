<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\SalesOrderDetailComponent;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesOrderDetailTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeSalesOrder(): SalesOrder
    {
        $customer = Customer::create([
            'code' => 'CUS-01',
            'name' => 'PT Maju Jaya',
            'address' => 'Jl. Test 12',
            'email' => 'customer@example.com',
            'phone' => '08123456',
            'pic_name' => 'Budi',
        ]);

        $product = Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel'])->id,
            'unit_id' => Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs'])->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
        ]);

        $order = SalesOrder::create([
            'number' => 'SO-202608-000001',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_APPROVED,
            'subtotal' => 250000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 250000,
            'notes' => 'Kirim cepat',
            'created_by' => auth()->id(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => 5,
            'unit_price' => 50000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => 250000,
        ]);

        return $order;
    }

    public function test_super_admin_can_view_sales_order_detail_page(): void
    {
        $this->actingAsAdmin();
        $order = $this->makeSalesOrder();

        $this->get(route('sales.orders.show', $order))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee('PT Maju Jaya')
            ->assertSee('Order Items')
            ->assertSee('Export PDF');
    }

    public function test_detail_page_shows_customer_info_and_items(): void
    {
        $this->actingAsAdmin();
        $order = $this->makeSalesOrder();

        Livewire::test(SalesOrderDetailComponent::class, ['salesOrder' => $order])
            ->assertSee('PT Maju Jaya')
            ->assertSee('customer@example.com')
            ->assertSee('Kaos')
            ->assertSee('Kirim cepat');
    }

    public function test_export_pdf_returns_pdf_download(): void
    {
        $this->actingAsAdmin();
        $order = $this->makeSalesOrder();

        Livewire::test(SalesOrderDetailComponent::class, ['salesOrder' => $order])
            ->call('exportPdf')
            ->assertOk();
    }

    public function test_print_dispatches_print_event(): void
    {
        $this->actingAsAdmin();
        $order = $this->makeSalesOrder();

        Livewire::test(SalesOrderDetailComponent::class, ['salesOrder' => $order])
            ->call('print')
            ->assertDispatched('print');
    }

    public function test_detail_page_renders_laser_print_area_with_company_header(): void
    {
        $this->actingAsAdmin();
        $order = $this->makeSalesOrder();

        Livewire::test(SalesOrderDetailComponent::class, ['salesOrder' => $order])
            ->assertSee('print-area print-laser')
            ->assertSee(config('app.name'))
            ->assertSee('Sales Order');
    }
}
