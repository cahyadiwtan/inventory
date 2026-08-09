<div class="flex flex-col gap-6">
    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-navy-900">Quotations</h1>
            <p class="mt-1 max-w-2xl text-lg text-gray-500">Kelola dan pantau penawaran harga pelanggan</p>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            <button wire:click="export" class="flex h-10 items-center justify-center gap-2 rounded-lg border border-surface-outline bg-white px-4 text-sm font-semibold text-navy-800 shadow-sm transition-colors hover:bg-surface-muted">
                <span class="material-symbols-outlined text-[18px]">download</span>
                Export
            </button>
            <a href="{{ route('sales.quotations.create') }}" wire:navigate class="flex h-10 items-center justify-center gap-2 rounded-lg bg-royal px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-royal-600">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Quotation
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="relative overflow-hidden rounded-lg border border-surface-outline/30 bg-white p-4 shadow-sm">
            <div class="mb-2 flex items-start justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Draft Quotes</h3>
                <span class="material-symbols-outlined rounded-full bg-surface p-1.5 text-[20px] text-gray-500">draft</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-navy-900">{{ number_format($draftCount) }}</span>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-surface-dim"></div>
        </div>

        <div class="relative overflow-hidden rounded-lg border border-surface-outline/30 bg-white p-4 shadow-sm">
            <div class="mb-2 flex items-start justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Awaiting Approval</h3>
                <span class="material-symbols-outlined rounded-full bg-royal/10 p-1.5 text-[20px] text-royal-600">pending_actions</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-navy-900">{{ number_format($awaitingCount) }}</span>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-surface-dim"></div>
        </div>

        <div class="relative overflow-hidden rounded-lg border border-surface-outline/30 bg-white p-4 shadow-sm">
            <div class="mb-2 flex items-start justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Conversion Rate</h3>
                <span class="material-symbols-outlined rounded-full bg-royal/10 p-1.5 text-[20px] text-royal-600">show_chart</span>
            </div>
            <div class="flex items-end justify-between">
                <span class="text-3xl font-bold text-navy-900">{{ $conversionRate }}%</span>
                <div class="flex items-center gap-1 rounded bg-royal-50 px-1.5 text-xs font-semibold text-royal-700">
                    <span class="material-symbols-outlined text-[14px]">trending_up</span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-surface-dim"></div>
        </div>

        <div class="relative overflow-hidden rounded-lg border border-surface-outline/30 bg-white p-4 shadow-sm">
            <div class="mb-2 flex items-start justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Open Quote Value</h3>
                <span class="material-symbols-outlined rounded-full bg-royal/10 p-1.5 text-[20px] text-royal-600">payments</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-navy-900">{{ number_format($openValue, 0) }}</span>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-surface-dim"></div>
        </div>
    </div>

    <div class="flex flex-col overflow-hidden rounded-lg border border-surface-outline/30 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-surface-outline/30 bg-surface-muted/50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex w-full items-center rounded-lg border border-surface-outline/50 bg-white px-4 sm:w-80 focus-within:border-royal focus-within:ring-1 focus-within:ring-royal/20">
                <span class="material-symbols-outlined mr-2 text-[20px] text-gray-500">search</span>
                <input wire:model.live="search" type="text" placeholder="Cari nomor, customer..." class="w-full border-none bg-transparent py-2 text-sm text-navy-900 outline-none placeholder:text-gray-400">
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="statusFilter" class="h-8 rounded-md border border-surface-outline/50 bg-white px-2 text-xs font-medium text-gray-500">
                    <option value="">Semua Status</option>
                    @foreach (['draft', 'sent', 'accepted', 'rejected', 'expired'] as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] border-collapse text-left">
                <thead>
                    <tr class="border-b border-surface-outline/30 bg-surface-muted/50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="p-4 font-semibold">Quote Number</th>
                        <th class="p-4 font-semibold">Customer</th>
                        <th class="p-4 font-semibold">Date</th>
                        <th class="p-4 font-semibold">Valid Until</th>
                        <th class="p-4 text-right font-semibold">Total Amount</th>
                        <th class="p-4 text-center font-semibold">Status</th>
                        <th class="p-4 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-outline/20 bg-white text-sm text-navy-900">
                    @forelse ($quotations as $quotation)
                        <tr class="group cursor-pointer transition-colors hover:bg-surface-muted/50">
                            <td class="p-4 text-sm font-semibold text-royal group-hover:underline">{{ $quotation->number }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-royal-50 font-semibold text-royal-700">
                                        {{ strtoupper(substr($quotation->customer?->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-medium text-navy-900">{{ $quotation->customer?->name }}</div>
                                        <div class="truncate text-xs text-gray-500">{{ $quotation->customer?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap p-4 text-gray-500">{{ $quotation->quotation_date?->format('d M Y') }}</td>
                            <td class="whitespace-nowrap p-4 {{ $quotation->isExpired() ? 'text-danger' : 'text-gray-500' }}">{{ $quotation->valid_until?->format('d M Y') }}</td>
                            <td class="p-4 text-right font-medium">{{ number_format((float) $quotation->total, 2) }}</td>
                            <td class="p-4 text-center">
                                @php
                                    $badge = match ($quotation->status) {
                                        'draft' => 'bg-surface-muted text-gray-600',
                                        'sent' => 'bg-royal-50 text-royal-700',
                                        'accepted' => 'bg-success-soft text-success',
                                        'rejected' => 'bg-danger-soft text-danger',
                                        'expired' => 'bg-danger-soft/60 text-danger/80',
                                        default => 'bg-surface-muted text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">{{ ucfirst($quotation->status) }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <a href="{{ route('sales.quotations.show', $quotation) }}" title="Lihat detail" class="rounded p-1.5 text-gray-500 transition-colors hover:bg-surface-muted hover:text-royal">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </a>
                                @if (in_array($quotation->status, ['draft', 'sent']))
                                    <a href="{{ route('sales.quotations.edit', $quotation) }}" wire:navigate title="Edit" class="rounded p-1.5 text-gray-500 transition-colors hover:bg-surface-muted hover:text-royal">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                @endif
                                @if ($quotation->status === 'draft')
                                    <button wire:click="send('{{ $quotation->id }}')" title="Kirim" class="rounded p-1.5 text-gray-500 transition-colors hover:bg-surface-muted hover:text-royal">
                                        <span class="material-symbols-outlined text-[18px]">send</span>
                                    </button>
                                    <button wire:click="expire('{{ $quotation->id }}')" title="Kedaluwarsa" class="rounded p-1.5 text-gray-500 transition-colors hover:bg-surface-muted hover:text-danger">
                                        <span class="material-symbols-outlined text-[18px]">schedule</span>
                                    </button>
                                @elseif ($quotation->status === 'sent')
                                    <button wire:click="accept('{{ $quotation->id }}')" title="Terima" class="rounded p-1.5 text-gray-500 transition-colors hover:bg-surface-muted hover:text-success">
                                        <span class="material-symbols-outlined text-[18px]">check</span>
                                    </button>
                                    <button wire:click="reject('{{ $quotation->id }}')" wire:confirm="Yakin menolak quotation ini?" title="Tolak" class="rounded p-1.5 text-gray-500 transition-colors hover:bg-surface-muted hover:text-danger">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                @elseif ($quotation->isConvertible())
                                    <button wire:click="convert('{{ $quotation->id }}')" wire:confirm="Konversi ke Sales Order?" title="Konversi ke SO" class="rounded p-1.5 text-gray-500 transition-colors hover:bg-surface-muted hover:text-royal">
                                        <span class="material-symbols-outlined text-[18px]">swap_vert</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-sm text-gray-500">Tidak ada quotation ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-surface-outline/30 bg-surface-muted/50 p-3 px-4 text-sm text-gray-500">
            <div>Menampilkan {{ $quotations->firstItem() ?? 0 }}-{{ $quotations->lastItem() ?? 0 }} dari {{ $quotations->total() }} quotation</div>
            <div class="flex items-center gap-2">
                @if ($quotations->hasPages())
                    <button wire:click="previousPage" @disabled($quotations->onFirstPage()) class="rounded p-1 transition-colors hover:bg-surface-muted disabled:opacity-50">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </button>
                    <button wire:click="nextPage" @disabled($quotations->onLastPage()) class="rounded p-1 transition-colors hover:bg-surface-muted disabled:opacity-50">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="relative h-48 overflow-hidden rounded-lg border border-surface-outline/30 shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-r from-navy-900 to-navy-700"></div>
        <div class="relative z-10 flex h-full flex-col justify-center p-8">
            <h2 class="mb-1 text-xl font-semibold text-white">Siap menutup lebih banyak transaksi?</h2>
            <p class="mb-4 max-w-md text-sm text-navy-200">Pantau status quotation secara real-time dan konversi penawaran yang diterima menjadi Sales Order dengan satu klik.</p>
            <a href="{{ route('sales.quotations.create') }}" wire:navigate class="flex h-9 w-fit items-center justify-center gap-2 rounded-lg bg-white px-4 text-sm font-semibold text-navy-900 transition-colors hover:bg-surface-muted">
                Buat Quotation Baru
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
    </div>
</div>
