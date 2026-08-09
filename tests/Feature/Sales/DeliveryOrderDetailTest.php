<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\DeliveryOrderDetailComponent;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryOrderDetailTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeDeliveryOrder(): DeliveryOrder
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
            'subtotal' => 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 50000,
            'created_by' => auth()->id(),
        ]);

        $soItem = $order->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => 5,
            'unit_price' => 50000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => 250000,
        ]);

        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Gudang A', 'address' => 'Jl. A']);

        $delivery = DeliveryOrder::create([
            'number' => 'DO-202608-000001',
            'sales_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'delivery_date' => now()->toDateString(),
            'status' => DeliveryOrder::STATUS_POSTED,
            'notes' => 'Kirim cepat',
            'created_by' => auth()->id(),
        ]);

        $delivery->items()->create([
            'sales_order_item_id' => $soItem->id,
            'product_id' => $product->id,
            'qty' => 3,
        ]);

        return $delivery;
    }

    public function test_super_admin_can_view_delivery_detail_page(): void
    {
        $this->actingAsAdmin();
        $delivery = $this->makeDeliveryOrder();

        $this->get(route('sales.deliveries.show', $delivery))
            ->assertOk()
            ->assertSee($delivery->number)
            ->assertSee('PT Maju Jaya')
            ->assertSee('Delivery Items')
            ->assertSee('Export PDF');
    }

    public function test_detail_page_shows_customer_info_and_items(): void
    {
        $this->actingAsAdmin();
        $delivery = $this->makeDeliveryOrder();

        Livewire::test(DeliveryOrderDetailComponent::class, ['deliveryOrder' => $delivery])
            ->assertSee('PT Maju Jaya')
            ->assertSee('customer@example.com')
            ->assertSee('Kaos')
            ->assertSee('Gudang A')
            ->assertSee('Kirim cepat');
    }

    public function test_export_pdf_returns_pdf_download(): void
    {
        $this->actingAsAdmin();
        $delivery = $this->makeDeliveryOrder();

        Livewire::test(DeliveryOrderDetailComponent::class, ['deliveryOrder' => $delivery])
            ->call('exportPdf')
            ->assertOk();
    }

    public function test_print_dispatches_print_event(): void
    {
        $this->actingAsAdmin();
        $delivery = $this->makeDeliveryOrder();

        Livewire::test(DeliveryOrderDetailComponent::class, ['deliveryOrder' => $delivery])
            ->call('print')
            ->assertDispatched('print');
    }

    public function test_page_has_laser_and_dot_matrix_print_modes(): void
    {
        $this->actingAsAdmin();
        $delivery = $this->makeDeliveryOrder();

        $this->get(route('sales.deliveries.show', $delivery))
            ->assertOk()
            ->assertSee('Laser / A4')
            ->assertSee('Dot Matrix')
            ->assertSee('print-area print-laser')
            ->assertSee('print-area print-matrix');
    }

    public function test_dot_matrix_mode_renders_continuous_layout(): void
    {
        $this->actingAsAdmin();
        $delivery = $this->makeDeliveryOrder();

        Livewire::test(DeliveryOrderDetailComponent::class, ['deliveryOrder' => $delivery])
            ->set('printMode', 'matrix')
            ->assertSet('printMode', 'matrix')
            ->assertSee('Delivery Order')
            ->assertSee($delivery->number)
            ->assertSee('Kaos')
            ->assertSee('Gudang A')
            ->assertSee('Total Qty');
    }
}
