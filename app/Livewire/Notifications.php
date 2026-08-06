<?php

namespace App\Livewire;

use App\Models\AppNotification;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public function markAllRead(): void
    {
        AppNotification::query()
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function markRead(string $id): void
    {
        AppNotification::query()
            ->where('id', $id)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function render()
    {
        $query = AppNotification::query()
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', auth()->id()));

        if ($this->filter === 'unread') {
            $query->where('is_read', false);
        }

        return view('livewire.notifications', [
            'notifications' => $query->with('subject')->orderByDesc('created_at')->paginate(20),
        ])->title('Notifikasi | Inventory System');
    }
}
