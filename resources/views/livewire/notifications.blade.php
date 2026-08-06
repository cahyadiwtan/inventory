<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Notifikasi</h1>
            <p class="mt-1 text-sm text-gray-500">Peringatan stok menipis, dokumen jatuh tempo, dan pembaruan sistem.</p>
        </div>
        <button wire:click="markAllRead" class="btn-secondary">Tandai semua dibaca</button>
    </div>

    <div class="flex gap-2">
        <button wire:click="$set('filter', 'all')"
            class="rounded-md px-4 py-1.5 text-sm font-medium transition-colors {{ $filter === 'all' ? 'bg-royal text-white' : 'bg-white text-navy-700 border border-surface-outline' }}">
            Semua
        </button>
        <button wire:click="$set('filter', 'unread')"
            class="rounded-md px-4 py-1.5 text-sm font-medium transition-colors {{ $filter === 'unread' ? 'bg-royal text-white' : 'bg-white text-navy-700 border border-surface-outline' }}">
            Belum dibaca
        </button>
    </div>

    <div class="card divide-y divide-surface-border">
        @forelse ($notifications as $notification)
            <div class="flex items-start gap-3 px-6 py-4 {{ $notification->is_read ? '' : 'bg-surface-muted/40' }}">
                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->is_read ? 'bg-gray-300' : 'bg-royal' }}"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-4">
                        <div class="text-sm font-semibold text-navy-900">{{ $notification->title }}</div>
                        <div class="text-xs text-gray-400">{{ $notification->created_at?->diffForHumans() }}</div>
                    </div>
                    <div class="mt-0.5 text-sm text-navy-600">{{ $notification->message }}</div>
                    <div class="mt-1 text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $notification->type ?? '')) }}</div>
                </div>
                @unless ($notification->is_read)
                    <button wire:click="markRead('{{ $notification->id }}')" class="text-xs font-medium text-royal hover:text-royal-600">Tandai dibaca</button>
                @endunless
            </div>
        @empty
            <div class="px-6 py-16 text-center text-sm text-gray-500">
                Tidak ada {{ $filter === 'unread' ? 'notifikasi belum dibaca' : 'notifikasi' }}.
            </div>
        @endforelse
    </div>

    <div>
        {{ $notifications->links() }}
    </div>
</div>
