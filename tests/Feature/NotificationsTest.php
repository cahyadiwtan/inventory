<?php

namespace Tests\Feature;

use App\Livewire\Notifications;
use App\Models\AppNotification;
use App\Models\Product;
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
}
