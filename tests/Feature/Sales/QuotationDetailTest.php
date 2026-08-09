<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\QuotationDetailComponent;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Quotation;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationDetailTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeQuotation(): Quotation
    {
        $customer = Customer::create([
            'code' => 'CUS-01',
            'name' => 'PT Maju Jaya',
            'address' => 'Jl. Test 12',
            'email' => 'customer@example.com',
            'phone' => '08123456',
            'pic_name' => 'Budi',
            'payment_term_days' => 14,
        ]);

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

    public function test_super_admin_can_view_quotation_detail_page(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        $this->get(route('sales.quotations.show', $quotation))
            ->assertOk()
            ->assertSee($quotation->number)
            ->assertSee('PT Maju Jaya')
            ->assertSee('Line Items');
    }

    public function test_detail_page_shows_customer_info_and_items(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        Livewire::test(QuotationDetailComponent::class, ['quotation' => $quotation])
            ->assertSee('PT Maju Jaya')
            ->assertSee('customer@example.com')
            ->assertSee('Kaos')
            ->assertSee('50,000.00');
    }

    public function test_export_pdf_returns_pdf_download(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        Livewire::test(QuotationDetailComponent::class, ['quotation' => $quotation])
            ->call('exportPdf')
            ->assertOk();
    }

    public function test_detail_page_renders_laser_print_area_with_company_header(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        Livewire::test(QuotationDetailComponent::class, ['quotation' => $quotation])
            ->assertSee('print-area print-laser')
            ->assertSee(config('app.name'))
            ->assertSee('Quotation');
    }
}
