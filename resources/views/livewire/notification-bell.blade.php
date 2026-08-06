<div x-data="{ open: false }" class="relative">
    <button @click="open = ! open" type="button" class="relative rounded-lg p-2 text-navy-500 transition-colors hover:bg-surface-muted hover:text-navy-900">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($unread > 0)
            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-danger px-1 text-xs font-bold text-white">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 top-11 z-50 w-80 rounded-lg border border-surface-outline bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-surface-border px-4 py-3">
            <span class="text-sm font-semibold text-navy-900">Notifikasi</span>
            <button wire:click="markAllRead" class="text-xs font-medium text-royal hover:text-royal-600">Tandai semua dibaca</button>
        </div>
        <div class="max-h-96 overflow-y-auto">
            @forelse ($notifications as $notification)
                <div class="border-b border-surface-border px-4 py-3 {{ $notification->is_read ? 'opacity-60' : 'bg-surface-muted/60' }}">
                    <div class="flex items-start gap-2">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $notification->is_read ? 'bg-gray-300' : 'bg-royal' }}"></span>
                        <div>
                            <div class="text-sm font-semibold text-navy-900">{{ $notification->title }}</div>
                            <div class="mt-0.5 text-xs text-navy-500">{{ $notification->message }}</div>
                            <div class="mt-1 text-[11px] text-gray-400">{{ $notification->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>
            @endforelse
        </div>
    </div>
</div>
