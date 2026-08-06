<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\QuotationComponent;
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

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'code' => 'CUS-01',
            'name' => 'PT Maju Jaya',
            'address' => 'Jl. Test',
            'payment_term_days' => 14,
        ]);
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

    private function makeQuotation(?Product $product = null): Quotation
    {
        $customer = $this->makeCustomer();
        $product ??= $this->makeProduct();

        $quotation = Quotation::create([
            'number' => 'QT-202608-000001',
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'status' => Quotation::STATUS_DRAFT,
            'subtotal' => 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 50000,
            'created_by' => auth()->id(),
        ]);

        $quotation->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => 1,
            'unit_price' => 50000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => 50000,
        ]);

        return $quotation;
    }

    public function test_super_admin_can_view_quotation_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('sales.quotations.index'))
            ->assertOk()
            ->assertSee('Quotation');
    }

    public function test_create_quotation_stores_document_and_totals(): void
    {
        $this->actingAsAdmin();
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();

        Livewire::test(QuotationComponent::class)
            ->set('customerId', $customer->id)
            ->set('quotationDate', now()->toDateString())
            ->set('validUntil', now()->addDays(30)->toDateString())
            ->set('items', [[
                'product_id' => $product->id,
                'qty' => 2,
                'unit_price' => 100000,
                'discount' => 10,
                'tax_id' => null,
            ]])
            ->call('save');

        $this->assertDatabaseHas('quotations', [
            'customer_id' => $customer->id,
            'status' => 'draft',
            'subtotal' => 200000,
            'discount_amount' => 20000,
            'total' => 180000,
        ]);

        $this->assertDatabaseHas('quotation_items', [
            'product_id' => $product->id,
            'qty' => 2,
            'line_total' => 180000,
        ]);
    }

    public function test_send_accept_reject_lifecycle(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        Livewire::test(QuotationComponent::class)->call('send', $quotation->id);

        $this->assertEquals('sent', $quotation->fresh()->status);

        Livewire::test(QuotationComponent::class)->call('accept', $quotation->id);
        $this->assertEquals('accepted', $quotation->fresh()->status);

        $quotation->update(['status' => Quotation::STATUS_SENT]);
        Livewire::test(QuotationComponent::class)->call('reject', $quotation->id);
        $this->assertEquals('rejected', $quotation->fresh()->status);
    }

    public function test_accept_only_allowed_from_sent(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        Livewire::test(QuotationComponent::class)->call('accept', $quotation->id);

        $this->assertEquals('draft', $quotation->fresh()->status);
    }

    public function test_expire_sets_status(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        Livewire::test(QuotationComponent::class)->call('expire', $quotation->id);

        $this->assertEquals('expired', $quotation->fresh()->status);
    }

    public function test_convert_accepted_quotation_creates_sales_order(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();
        $quotation->update(['status' => Quotation::STATUS_ACCEPTED]);

        Livewire::test(QuotationComponent::class)->call('convert', $quotation->id);

        $this->assertDatabaseHas('sales_orders', [
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'status' => SalesOrder::STATUS_DRAFT,
        ]);

        $order = SalesOrder::where('quotation_id', $quotation->id)->first();
        $this->assertCount(1, $order->items);
    }

    public function test_convert_rejected_quotation_is_ignored(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();
        $quotation->update(['status' => Quotation::STATUS_REJECTED]);

        Livewire::test(QuotationComponent::class)->call('convert', $quotation->id);

        $this->assertDatabaseCount('sales_orders', 0);
    }

    public function test_validation_requires_customer_and_items(): void
    {
        $this->actingAsAdmin();

        Livewire::test(QuotationComponent::class)
            ->set('quotationDate', now()->toDateString())
            ->set('validUntil', now()->addDays(30)->toDateString())
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['customerId' => 'required', 'items' => 'required']);
    }
}
