<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\QuotationCreateComponent;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Quotation;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationCreateTest extends TestCase
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

    public function test_super_admin_can_view_create_page(): void
    {
        $this->actingAsAdmin();

        $this->get(route('sales.quotations.create'))
            ->assertOk()
            ->assertSee('New Quotation')
            ->assertSee('Line Items');
    }

    public function test_save_stores_draft_quotation_and_redirects_to_detail(): void
    {
        $this->actingAsAdmin();
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();

        $response = Livewire::test(QuotationCreateComponent::class)
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
            'status' => Quotation::STATUS_DRAFT,
            'subtotal' => 200000,
            'discount_amount' => 20000,
            'tax_amount' => 0,
            'total' => 180000,
        ]);

        $this->assertDatabaseHas('quotation_items', [
            'product_id' => $product->id,
            'qty' => 2,
            'line_total' => 180000,
        ]);

        $quotation = Quotation::where('customer_id', $customer->id)->first();
        $response->assertRedirect(route('sales.quotations.show', $quotation));
    }

    public function test_save_and_send_stores_sent_quotation(): void
    {
        $this->actingAsAdmin();
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();

        Livewire::test(QuotationCreateComponent::class)
            ->set('customerId', $customer->id)
            ->set('quotationDate', now()->toDateString())
            ->set('validUntil', now()->addDays(30)->toDateString())
            ->set('items', [[
                'product_id' => $product->id,
                'qty' => 1,
                'unit_price' => 50000,
                'discount' => 0,
                'tax_id' => null,
            ]])
            ->call('saveAndSend');

        $this->assertDatabaseHas('quotations', [
            'customer_id' => $customer->id,
            'status' => Quotation::STATUS_SENT,
            'total' => 50000,
        ]);
    }

    public function test_add_and_remove_item_updates_rows(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();

        Livewire::test(QuotationCreateComponent::class)
            ->call('addItem')
            ->assertSet('items', [[
                'product_id' => '',
                'qty' => 1,
                'unit_price' => 0,
                'discount' => 0,
                'tax_id' => null,
            ]])
            ->call('removeItem', 0)
            ->assertSet('items', []);
    }

    public function test_validation_requires_customer_and_items(): void
    {
        $this->actingAsAdmin();

        Livewire::test(QuotationCreateComponent::class)
            ->set('quotationDate', now()->toDateString())
            ->set('validUntil', now()->addDays(30)->toDateString())
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['customerId' => 'required', 'items' => 'required']);
    }

    public function test_selecting_product_autofills_unit_price(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeProduct();

        Livewire::test(QuotationCreateComponent::class)
            ->call('addItem')
            ->set('items.0.product_id', $product->id)
            ->assertSet('items.0.unit_price', 50000);
    }
}
