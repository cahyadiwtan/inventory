<div class="flex flex-col gap-6" x-data="{ mode: @entangle('printMode') }" :class="mode === 'matrix' ? 'print-mode-matrix' : 'print-mode-laser'" @print.window="window.print()">
    <div class="print-screen flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between print:hidden">
        <div>
            <a href="{{ route('sales.deliveries.index') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm font-medium text-gray-500 transition-colors hover:text-royal">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Delivery Orders
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-navy-900">{{ $deliveryOrder->number }}</h1>
                @php
                    $badge = match ($deliveryOrder->status) {
                        'draft' => 'bg-surface-muted text-gray-600',
                        'posted' => 'bg-success-soft text-success',
                        'cancelled' => 'bg-danger-soft text-danger',
                        default => 'bg-surface-muted text-gray-600',
                    };
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">{{ ucfirst($deliveryOrder->status) }}</span>
            </div>
            <p class="mt-1 text-lg text-gray-500">Customer: {{ $deliveryOrder->salesOrder?->customer?->name }}</p>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            <div class="flex items-center gap-1 rounded-lg border border-surface-outline bg-white p-1 text-sm shadow-sm">
                <button @click="mode = 'laser'" :class="mode === 'laser' ? 'bg-royal text-white' : 'text-gray-600 hover:bg-surface-muted'" class="rounded-md px-3 py-1.5 font-semibold transition-colors">Laser / A4</button>
                <button @click="mode = 'matrix'" :class="mode === 'matrix' ? 'bg-royal text-white' : 'text-gray-600 hover:bg-surface-muted'" class="rounded-md px-3 py-1.5 font-semibold transition-colors">Dot Matrix</button>
            </div>
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
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Delivery Date</p>
            <p class="mt-1 text-lg font-semibold text-navy-900">{{ $deliveryOrder->delivery_date?->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Sales Order</p>
            <p class="mt-1 text-lg font-semibold text-navy-900">{{ $deliveryOrder->salesOrder?->number }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Warehouse</p>
            <p class="mt-1 text-lg font-semibold text-navy-900">{{ $deliveryOrder->warehouse?->name }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Prepared By</p>
            <div class="mt-1 flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-royal-50 text-xs font-semibold text-royal-700">
                    {{ strtoupper(substr($deliveryOrder->creator?->name ?? '?', 0, 2)) }}
                </div>
                <p class="text-sm text-navy-900">{{ $deliveryOrder->creator?->name }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="flex flex-col gap-6 xl:col-span-8">
            <div class="overflow-hidden rounded-lg border border-surface-outline/30 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-surface-outline/30 bg-surface-muted/50 px-6 py-4">
                    <h2 class="text-lg font-semibold text-navy-900">Delivery Items</h2>
                    <span class="text-xs font-medium text-gray-500">{{ $deliveryOrder->items->count() }} item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] border-collapse text-left text-sm text-navy-900">
                        <thead>
                            <tr class="border-b border-surface-outline/30 bg-surface-muted/50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="w-12 p-4 text-center">#</th>
                                <th class="p-4">Product / SKU</th>
                                <th class="p-4 text-right">Order Qty</th>
                                <th class="p-4 text-right">Delivered</th>
                                <th class="p-4 text-right">This Delivery</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-outline/20">
                            @forelse ($deliveryOrder->items as $index => $item)
                                @php
                                    $soItem = $item->salesOrderItem;
                                    $deliveredBefore = $soItem ? round($salesService->deliveredQty($soItem) - (float) $item->qty, 2) : 0;
                                @endphp
                                <tr class="transition-colors hover:bg-surface-muted/50">
                                    <td class="p-4 text-center text-gray-500">{{ $index + 1 }}</td>
                                    <td class="p-4">
                                        <div class="font-medium text-navy-900">{{ $item->product?->name ?: $soItem?->description }}</div>
                                        <div class="text-xs text-gray-500">SKU: {{ $item->product?->code }}</div>
                                    </td>
                                    <td class="p-4 text-right">{{ number_format((float) ($soItem?->qty ?? 0), 2) }}</td>
                                    <td class="p-4 text-right">{{ number_format($deliveredBefore, 2) }}</td>
                                    <td class="p-4 text-right font-medium">{{ number_format((float) $item->qty, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-sm text-gray-500">Tidak ada item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end border-t border-surface-outline/30 bg-surface-muted/20 px-6 py-4">
                    <div class="w-72 space-y-2 text-sm">
                        <div class="flex justify-between border-t border-surface-outline/50 pt-2 text-lg font-bold text-navy-900">
                            <span>Total Qty</span>
                            <span>{{ number_format((float) $deliveryOrder->items->sum('qty'), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($deliveryOrder->notes)
                <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-navy-900">Notes</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $deliveryOrder->notes }}</p>
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-6 xl:col-span-4">
            <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-2 border-b border-surface-outline/30 pb-3">
                    <span class="material-symbols-outlined text-[24px] text-royal-600">local_shipping</span>
                    <h3 class="text-base font-semibold text-navy-900">Ship To</h3>
                </div>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Company</p>
                        <p class="mt-1 font-medium text-navy-900">{{ $deliveryOrder->salesOrder?->customer?->name }}</p>
                        @if ($deliveryOrder->salesOrder?->customer?->address)
                            <p class="mt-1 whitespace-pre-line text-gray-500">{{ $deliveryOrder->salesOrder->customer->address }}</p>
                        @endif
                    </div>
                    @if ($deliveryOrder->salesOrder?->customer?->pic_name)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Contact Person</p>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-royal-50 text-xs font-semibold text-royal-700">
                                    {{ strtoupper(substr($deliveryOrder->salesOrder->customer->pic_name, 0, 2)) }}
                                </div>
                                <p class="font-medium text-navy-900">{{ $deliveryOrder->salesOrder->customer->pic_name }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex flex-col gap-2 pt-1">
                        @if ($deliveryOrder->salesOrder?->customer?->email)
                            <div class="flex items-center gap-2 text-navy-800">
                                <span class="material-symbols-outlined text-[16px] text-gray-500">mail</span>
                                {{ $deliveryOrder->salesOrder->customer->email }}
                            </div>
                        @endif
                        @if ($deliveryOrder->salesOrder?->customer?->phone)
                            <div class="flex items-center gap-2 text-navy-800">
                                <span class="material-symbols-outlined text-[16px] text-gray-500">phone</span>
                                {{ $deliveryOrder->salesOrder->customer->phone }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-2 border-b border-surface-outline/30 pb-3">
                    <span class="material-symbols-outlined text-[24px] text-royal-600">shopping_bag</span>
                    <h3 class="text-base font-semibold text-navy-900">Sales Order</h3>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Number</span>
                        <span class="font-medium text-navy-900">{{ $deliveryOrder->salesOrder?->number }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Order Date</span>
                        <span class="font-medium text-navy-900">{{ $deliveryOrder->salesOrder?->order_date?->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="rounded-md px-2 py-0.5 text-xs font-semibold
                            {{ $deliveryOrder->salesOrder?->status === 'approved' ? 'bg-success-soft text-success' : 'bg-surface-muted text-gray-600' }}">
                            {{ ucfirst($deliveryOrder->salesOrder?->status ?? '-') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- ==== Laser / A4 print area ==== --}}
    <div class="print-area print-laser">
        @include('pdf.partials.laser-delivery-order', ['deliveryOrder' => $deliveryOrder, 'salesService' => $salesService])
    </div>

    {{-- ==== Dot Matrix print area (9.5x11 continuous) ==== --}}
    <div class="print-area print-matrix">
        <div class="mx-center mx-brand">{{ \App\Support\CompanyProfile::name() }}</div>
        <div class="mx-center mx-title">Delivery Order</div>
        <div class="mx-center" style="font-weight:700;">{{ $deliveryOrder->number }}</div>
        <div class="mx-center" style="margin-bottom:4px;">{{ $deliveryOrder->delivery_date?->format('d M Y') }}</div>

        <div class="mx-row"><span class="k">Ship To</span><span>{{ $deliveryOrder->salesOrder?->customer?->name }}</span></div>
        <div class="mx-row"><span class="k">SO</span><span>{{ $deliveryOrder->salesOrder?->number }}</span></div>
        <div class="mx-row"><span class="k">WH</span><span>{{ $deliveryOrder->warehouse?->name }}</span></div>

        <div class="mx-items">
            <div class="mx-row hd">
                <span class="num">#</span>
                <span class="desc">Item</span>
                <span class="rt">Order</span>
                <span class="rt">Dlv</span>
                <span class="rt">Qty</span>
            </div>
            @forelse ($deliveryOrder->items as $index => $item)
                @php
                    $soItem = $item->salesOrderItem;
                    $deliveredBefore = $soItem ? round($salesService->deliveredQty($soItem) - (float) $item->qty, 2) : 0;
                @endphp
                <div class="mx-row">
                    <span class="num">{{ $index + 1 }}</span>
                    <span class="desc">{{ $item->product?->name ?: $soItem?->description }}</span>
                    <span class="rt">{{ number_format((float) ($soItem?->qty ?? 0), 2) }}</span>
                    <span class="rt">{{ number_format($deliveredBefore, 2) }}</span>
                    <span class="rt" style="font-weight:700;">{{ number_format((float) $item->qty, 2) }}</span>
                </div>
            @empty
                <div class="mx-row"><span>No items.</span></div>
            @endforelse
        </div>

        <div class="mx-sum mx-row">
            <span class="k">Total Qty</span>
            <span>{{ number_format((float) $deliveryOrder->items->sum('qty'), 2) }}</span>
        </div>

        @if ($deliveryOrder->notes)
            <div class="mx-row" style="margin-top:2px;"><span class="k">Notes</span></div>
            <div>{{ $deliveryOrder->notes }}</div>
        @endif

        <div class="mx-sign">
            <div class="col">
                <div class="lbl">Prepared By</div>
                <div class="line"></div>
                <div class="mx-center">{{ $deliveryOrder->creator?->name }}</div>
            </div>
            <div class="col">
                <div class="lbl">Received By</div>
                <div class="line"></div>
                <div class="mx-center">{{ $deliveryOrder->salesOrder?->customer?->name }}</div>
            </div>
        </div>

        <div class="mx-center mx-footer">{{ now()->format('d M Y H:i') }}</div>
    </div>
</div>
