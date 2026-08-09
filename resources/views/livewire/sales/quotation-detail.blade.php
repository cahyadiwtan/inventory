<div class="flex flex-col gap-6" x-data @print.window="window.print()">
    <div class="print-screen flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between print:hidden">
        <div>
            <a href="{{ route('sales.quotations.index') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm font-medium text-gray-500 transition-colors hover:text-royal">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Quotations
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-navy-900">{{ $quotation->number }}</h1>
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
            </div>
            <p class="mt-1 text-lg text-gray-500">Customer: {{ $quotation->customer?->name }}</p>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            @if (in_array($quotation->status, ['draft', 'sent']))
                <a href="{{ route('sales.quotations.edit', $quotation) }}" wire:navigate class="flex h-10 items-center justify-center gap-2 rounded-lg border border-surface-outline bg-white px-4 text-sm font-semibold text-navy-800 shadow-sm transition-colors hover:bg-surface-muted">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                    Edit
                </a>
            @endif
            <button wire:click="print" class="flex h-10 items-center justify-center gap-2 rounded-lg border border-surface-outline bg-white px-4 text-sm font-semibold text-navy-800 shadow-sm transition-colors hover:bg-surface-muted">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Print
            </button>
            <button wire:click="exportPdf" class="flex h-10 items-center justify-center gap-2 rounded-lg bg-royal px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-royal-600">
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                Export PDF
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 rounded-lg border border-surface-outline/30 bg-white p-4 shadow-sm lg:grid-cols-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Quotation Date</p>
            <p class="mt-1 text-lg font-semibold text-navy-900">{{ $quotation->quotation_date?->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Valid Until</p>
            <p class="mt-1 text-lg font-semibold {{ $quotation->isExpired() ? 'text-danger' : 'text-navy-900' }}">{{ $quotation->valid_until?->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Amount</p>
            <p class="mt-1 text-lg font-semibold text-navy-900">{{ number_format((float) $quotation->total, 2) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Sales Rep</p>
            <div class="mt-1 flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-royal-50 text-xs font-semibold text-royal-700">
                    {{ strtoupper(substr($quotation->creator?->name ?? '?', 0, 2)) }}
                </div>
                <p class="text-sm text-navy-900">{{ $quotation->creator?->name }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="flex flex-col gap-6 xl:col-span-8">
            <div class="overflow-hidden rounded-lg border border-surface-outline/30 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-surface-outline/30 bg-surface-muted/50 px-6 py-4">
                    <h2 class="text-lg font-semibold text-navy-900">Line Items</h2>
                    <span class="text-xs font-medium text-gray-500">{{ $quotation->items->count() }} item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] border-collapse text-left text-sm text-navy-900">
                        <thead>
                            <tr class="border-b border-surface-outline/30 bg-surface-muted/50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="w-12 p-4 text-center">#</th>
                                <th class="p-4">Product / SKU</th>
                                <th class="p-4 text-right">Qty</th>
                                <th class="p-4 text-right">Unit Price</th>
                                <th class="p-4 text-right">Disc</th>
                                <th class="p-4 text-right">Tax</th>
                                <th class="p-4 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-outline/20">
                            @forelse ($quotation->items as $index => $item)
                                <tr class="transition-colors hover:bg-surface-muted/50">
                                    <td class="p-4 text-center text-gray-500">{{ $index + 1 }}</td>
                                    <td class="p-4">
                                        <div class="font-medium text-navy-900">{{ $item->product?->name ?: $item->description }}</div>
                                        <div class="text-xs text-gray-500">SKU: {{ $item->product?->code }}</div>
                                    </td>
                                    <td class="p-4 text-right">{{ number_format((float) $item->qty, 2) }}</td>
                                    <td class="p-4 text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="p-4 text-right">{{ number_format((float) $item->discount, 0) }}%</td>
                                    <td class="p-4 text-right">{{ $item->tax ? $item->tax->rate.'%' : '—' }}</td>
                                    <td class="p-4 text-right font-medium">{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-10 text-center text-sm text-gray-500">Tidak ada item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end border-t border-surface-outline/30 bg-surface-muted/20 px-6 py-4">
                    <div class="w-72 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span>{{ number_format((float) $quotation->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Discount</span>
                            <span>-{{ number_format((float) $quotation->discount_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Tax</span>
                            <span>{{ number_format((float) $quotation->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-surface-outline/50 pt-2 text-lg font-bold text-navy-900">
                            <span>Total</span>
                            <span>{{ number_format((float) $quotation->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($quotation->notes)
                <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-navy-900">Notes</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $quotation->notes }}</p>
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-6 xl:col-span-4">
            <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-2 border-b border-surface-outline/30 pb-3">
                    <span class="material-symbols-outlined text-[24px] text-royal-600">corporate_fare</span>
                    <h3 class="text-base font-semibold text-navy-900">Customer Info</h3>
                </div>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Company</p>
                        <p class="mt-1 font-medium text-navy-900">{{ $quotation->customer?->name }}</p>
                        @if ($quotation->customer?->address)
                            <p class="mt-1 whitespace-pre-line text-gray-500">{{ $quotation->customer->address }}</p>
                        @endif
                    </div>
                    @if ($quotation->customer?->pic_name)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Contact Person</p>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-royal-50 text-xs font-semibold text-royal-700">
                                    {{ strtoupper(substr($quotation->customer->pic_name, 0, 2)) }}
                                </div>
                                <p class="font-medium text-navy-900">{{ $quotation->customer->pic_name }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex flex-col gap-2 pt-1">
                        @if ($quotation->customer?->email)
                            <div class="flex items-center gap-2 text-navy-800">
                                <span class="material-symbols-outlined text-[16px] text-gray-500">mail</span>
                                {{ $quotation->customer->email }}
                            </div>
                        @endif
                        @if ($quotation->customer?->phone)
                            <div class="flex items-center gap-2 text-navy-800">
                                <span class="material-symbols-outlined text-[16px] text-gray-500">phone</span>
                                {{ $quotation->customer->phone }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($quotation->salesOrder)
                <div class="rounded-lg border border-success/20 bg-success-soft/50 p-5 shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[24px] text-success">check_circle</span>
                        <h3 class="text-base font-semibold text-navy-900">Converted</h3>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        Quotation ini telah dikonversi menjadi
                        <span class="font-semibold text-navy-900">{{ $quotation->salesOrder->number }}</span>.
                    </p>
                    <a href="{{ route('sales.orders.index') }}" wire:navigate class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-royal hover:underline">
                        Lihat Sales Order
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
    </div>

    {{-- ==== Laser / A4 print area ==== --}}
    <div class="print-area print-laser">
        @include('pdf.partials.laser-quotation', ['quotation' => $quotation])
    </div>
</div>
