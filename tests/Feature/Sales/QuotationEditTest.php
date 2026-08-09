<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\QuotationEditComponent;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Quotation;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationEditTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeCustomer(string $code = 'CUS-01', string $name = 'PT Maju Jaya'): Customer
    {
        return Customer::create([
            'code' => $code,
            'name' => $name,
            'address' => 'Jl. Test',
            'payment_term_days' => 14,
        ]);
    }

    private function makeProduct(string $code = 'PRD-01'): Product
    {
        return Product::create([
            'code' => $code,
            'name' => 'Kaos',
            'category_id' => ProductCategory::create(['code' => 'CAT-'.$code, 'name' => 'Apparel'])->id,
            'unit_id' => Unit::create(['code' => 'PCS-'.$code, 'name' => 'Piece', 'symbol' => 'pcs'])->id,
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

    public function test_super_admin_can_view_edit_page(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        $this->get(route('sales.quotations.edit', $quotation))
            ->assertOk()
            ->assertSee('Edit Quotation')
            ->assertSee($quotation->number)
            ->assertSee('Kaos');
    }

    public function test_edit_page_is_not_available_for_terminal_statuses(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();
        $quotation->update(['status' => Quotation::STATUS_ACCEPTED]);

        $this->get(route('sales.quotations.edit', $quotation))
            ->assertNotFound();
    }

    public function test_save_updates_quotation_and_items(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();
        $customer = $this->makeCustomer('CUS-02', 'PT Baru');
        $product = $this->makeProduct('PRD-02');

        Livewire::test(QuotationEditComponent::class, ['quotation' => $quotation])
            ->set('customerId', $customer->id)
            ->set('quotationDate', now()->toDateString())
            ->set('validUntil', now()->addDays(15)->toDateString())
            ->set('items', [[
                'product_id' => $product->id,
                'qty' => 2,
                'unit_price' => 100000,
                'discount' => 10,
                'tax_id' => null,
            ]])
            ->call('save');

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'customer_id' => $customer->id,
            'status' => Quotation::STATUS_DRAFT,
            'subtotal' => 200000,
            'discount_amount' => 20000,
            'total' => 180000,
        ]);

        $this->assertDatabaseCount('quotation_items', 1);
        $this->assertDatabaseHas('quotation_items', [
            'quotation_id' => $quotation->id,
            'product_id' => $product->id,
            'qty' => 2,
            'line_total' => 180000,
        ]);
    }

    public function test_save_and_send_sets_status_sent(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();
        $product = $this->makeProduct('PRD-02');

        Livewire::test(QuotationEditComponent::class, ['quotation' => $quotation])
            ->set('items', [[
                'product_id' => $product->id,
                'qty' => 1,
                'unit_price' => 50000,
                'discount' => 0,
                'tax_id' => null,
            ]])
            ->call('saveAndSend');

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => Quotation::STATUS_SENT,
        ]);
    }
}
