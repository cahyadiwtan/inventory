<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\SalesOrderComponent;
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

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeAcceptedQuotation(): Quotation
    {
        $customer = Customer::create(['code' => 'CUS-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. Test']);
        $product = Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel'])->id,
            'unit_id' => Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs'])->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
        ]);

        $quotation = Quotation::create([
            'number' => 'QT-202608-000001',
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'status' => Quotation::STATUS_ACCEPTED,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 100000,
            'created_by' => auth()->id(),
        ]);
        $quotation->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => 2,
            'unit_price' => 50000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => 100000,
        ]);

        return $quotation;
    }

    public function test_super_admin_can_view_sales_order_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('sales.orders.index'))
            ->assertOk()
            ->assertSee('Sales Order');
    }

    public function test_convert_creates_order_from_accepted_quotation(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeAcceptedQuotation();

        Livewire::test(SalesOrderComponent::class)
            ->set('quotationId', $quotation->id)
            ->call('convert');

        $this->assertDatabaseHas('sales_orders', [
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'status' => SalesOrder::STATUS_DRAFT,
            'total' => 100000,
        ]);
    }

    public function test_convert_is_ignored_for_unconvertible_quotation(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeAcceptedQuotation();
        $quotation->update(['status' => Quotation::STATUS_SENT]);

        Livewire::test(SalesOrderComponent::class)
            ->set('quotationId', $quotation->id)
            ->call('convert');

        $this->assertDatabaseCount('sales_orders', 0);
    }

    public function test_approve_sets_order_approved(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeAcceptedQuotation();

        $order = SalesOrder::create([
            'number' => 'SO-202608-000001',
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        Livewire::test(SalesOrderComponent::class)->call('approve', $order->id);

        $this->assertEquals(SalesOrder::STATUS_APPROVED, $order->fresh()->status);
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => SalesOrder::class,
            'approvable_id' => $order->id,
            'action' => 'approve',
        ]);
    }

    public function test_cancel_sets_order_cancelled(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeAcceptedQuotation();

        $order = SalesOrder::create([
            'number' => 'SO-202608-000001',
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        Livewire::test(SalesOrderComponent::class)->call('cancel', $order->id);

        $this->assertEquals(SalesOrder::STATUS_CANCELLED, $order->fresh()->status);
    }
}
