<?php

namespace Tests\Feature;

use App\Livewire\Notifications;
use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('super admin');

        $this->actingAs($admin);

        return $admin;
    }

    public function test_notifications_page_renders(): void
    {
        $this->actingAsAdmin();

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notifikasi');
    }

    public function test_notifications_page_requires_authentication(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_service_creates_notification(): void
    {
        $user = $this->actingAsAdmin();

        $product = Product::factory()->create();

        app(NotificationService::class)->notify(
            type: 'low_stock',
            title: 'Stok menipis',
            message: 'Stok produk menipis',
            user: $user,
            subject: $product,
        );

        $this->assertDatabaseHas('notifications', [
            'type' => 'low_stock',
            'user_id' => $user->id,
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'is_read' => false,
        ]);
    }

    public function test_mark_all_read(): void
    {
        $user = $this->actingAsAdmin();

        AppNotification::create([
            'type' => 'low_stock',
            'title' => 'A',
            'message' => 'A',
            'user_id' => $user->id,
            'is_read' => false,
        ]);
        AppNotification::create([
            'type' => 'low_stock',
            'title' => 'B',
            'message' => 'B',
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        Livewire::test(Notifications::class)->call('markAllRead');

        $this->assertEquals(0, AppNotification::where('user_id', $user->id)->where('is_read', false)->count());
    }

    public function test_mark_single_read(): void
    {
        $user = $this->actingAsAdmin();

        $notification = AppNotification::create([
            'type' => 'low_stock',
            'title' => 'A',
            'message' => 'A',
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        Livewire::test(Notifications::class)->call('markRead', $notification->id);

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_unread_filter(): void
    {
        $user = $this->actingAsAdmin();

        AppNotification::create([
            'type' => 'low_stock',
            'title' => 'Baca',
            'message' => 'sudah dibaca',
            'user_id' => $user->id,
            'is_read' => true,
            'read_at' => now(),
        ]);
        AppNotification::create([
            'type' => 'overdue',
            'title' => 'Belum',
            'message' => 'belum dibaca',
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        Livewire::test(Notifications::class)
            ->set('filter', 'unread')
            ->assertSee('Belum')
            ->assertDontSee('sudah dibaca');
    }

    private function makeDueInvoice(string $number): SalesInvoice
    {
        $category = ProductCategory::create(['code' => 'CAT-01', 'name' => 'Apparel']);
        $unit = Unit::create(['code' => 'PCS', 'name' => 'Piece', 'symbol' => 'pcs']);
        Product::create([
            'code' => 'PRD-01',
            'name' => 'Kaos',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'selling_price' => 50000,
            'purchase_price' => 30000,
        ]);
        $customer = Customer::create(['code' => 'CUS-01', 'name' => 'PT Maju Jaya', 'address' => 'Jl. Test', 'payment_term_days' => 14]);

        $order = SalesOrder::create([
            'number' => 'SO-'.$number,
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_APPROVED,
            'subtotal' => 500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 500000,
            'created_by' => auth()->id(),
        ]);

        return SalesInvoice::create([
            'number' => $number,
            'sales_order_id' => $order->id,
            'customer_id' => $customer->id,
            'invoice_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => SalesInvoice::STATUS_PARTIAL,
            'subtotal' => 500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 500000,
            'paid_amount' => 100000,
            'created_by' => auth()->id(),
        ]);
    }

    public function test_check_notifications_command_creates_due_invoice_notification(): void
    {
        $this->actingAsAdmin();

        $invoice = $this->makeDueInvoice('INV-202608-000001');

        $this->artisan('inventory:notifications')->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'type' => 'invoice_due',
            'subject_type' => SalesInvoice::class,
            'subject_id' => $invoice->id,
        ]);
    }

    public function test_check_notifications_command_does_not_duplicate_same_day(): void
    {
        $this->actingAsAdmin();

        $invoice = $this->makeDueInvoice('INV-202608-000002');

        $this->artisan('inventory:notifications');
        $this->artisan('inventory:notifications');

        $this->assertEquals(1, AppNotification::where('type', 'invoice_due')->where('subject_id', $invoice->id)->count());
    }
}
