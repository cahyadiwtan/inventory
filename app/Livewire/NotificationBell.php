<?php

namespace App\Livewire;

use App\Models\AppNotification;
use App\Services\NotificationService;
use Livewire\Component;

class NotificationBell extends Component
{
    public function render()
    {
        return view('livewire.notification-bell', [
            'unread' => app(NotificationService::class)->unreadCount(auth()->id()),
            'notifications' => AppNotification::query()
                ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', auth()->id()))
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ]);
    }

    public function markAllRead(): void
    {
        AppNotification::query()
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
