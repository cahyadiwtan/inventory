<?php

namespace Tests\Feature\Sales;

use App\Livewire\Sales\SalesInvoiceDetailComponent;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesInvoiceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);
    }

    private function makeInvoice(): SalesInvoice
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
            'created_by' => auth()->id(),
        ]);

        $invoice = SalesInvoice::create([
            'number' => 'INV-202608-000001',
            'sales_order_id' => $order->id,
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => SalesInvoice::STATUS_POSTED,
            'notes' => 'Kirim cepat',
            'subtotal' => 250000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 250000,
            'paid_amount' => 100000,
            'created_by' => auth()->id(),
        ]);

        $invoice->items()->create([
            'product_id' => $product->id,
            'description' => 'Kaos',
            'qty' => 5,
            'unit_price' => 50000,
            'discount' => 0,
            'tax_id' => null,
            'line_total' => 250000,
        ]);

        Payment::create([
            'number' => 'PAY-202608-000001',
            'payable_type' => SalesInvoice::class,
            'payable_id' => $invoice->id,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference' => 'TRX-001',
            'amount' => 100000,
            'status' => Payment::STATUS_POSTED,
            'created_by' => auth()->id(),
        ]);

        return $invoice;
    }

    public function test_super_admin_can_view_sales_invoice_detail_page(): void
    {
        $this->actingAsAdmin();
        $invoice = $this->makeInvoice();

        $this->get(route('sales.invoices.show', $invoice))
            ->assertOk()
            ->assertSee($invoice->number)
            ->assertSee('PT Maju Jaya')
            ->assertSee('Invoice Items')
            ->assertSee('Export PDF');
    }

    public function test_detail_page_shows_customer_info_items_and_payments(): void
    {
        $this->actingAsAdmin();
        $invoice = $this->makeInvoice();

        Livewire::test(SalesInvoiceDetailComponent::class, ['invoice' => $invoice])
            ->assertSee('PT Maju Jaya')
            ->assertSee('customer@example.com')
            ->assertSee('Kaos')
            ->assertSee('Kirim cepat')
            ->assertSee('PAY-202608-000001')
            ->assertSee('bank transfer')
            ->assertSee('TRX-001')
            ->assertSee('Posted')
            ->assertSee('150,000.00');
    }

    public function test_export_pdf_returns_pdf_download(): void
    {
        $this->actingAsAdmin();
        $invoice = $this->makeInvoice();

        Livewire::test(SalesInvoiceDetailComponent::class, ['invoice' => $invoice])
            ->call('exportPdf')
            ->assertOk();
    }

    public function test_print_dispatches_print_event(): void
    {
        $this->actingAsAdmin();
        $invoice = $this->makeInvoice();

        Livewire::test(SalesInvoiceDetailComponent::class, ['invoice' => $invoice])
            ->call('print')
            ->assertDispatched('print');
    }

    public function test_page_has_laser_and_dot_matrix_print_modes(): void
    {
        $this->actingAsAdmin();
        $invoice = $this->makeInvoice();

        $this->get(route('sales.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Laser / A4')
            ->assertSee('Dot Matrix')
            ->assertSee('print-area print-laser')
            ->assertSee('print-area print-matrix');
    }

    public function test_dot_matrix_mode_renders_continuous_layout(): void
    {
        $this->actingAsAdmin();
        $invoice = $this->makeInvoice();

        Livewire::test(SalesInvoiceDetailComponent::class, ['invoice' => $invoice])
            ->set('printMode', 'matrix')
            ->assertSet('printMode', 'matrix')
            ->assertSee('Sales Invoice')
            ->assertSee($invoice->number)
            ->assertSee('Kaos')
            ->assertSee('PAY-202608-000001')
            ->assertSee('Balance');
    }
}
