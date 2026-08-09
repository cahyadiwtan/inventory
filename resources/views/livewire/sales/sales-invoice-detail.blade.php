<div class="flex flex-col gap-6" x-data="{ mode: @entangle('printMode') }" :class="mode === 'matrix' ? 'print-mode-matrix' : 'print-mode-laser'" @print.window="window.print()">
    <div class="print-screen flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between print:hidden">
        <div>
            <a href="{{ route('sales.invoices.index') }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm font-medium text-gray-500 transition-colors hover:text-royal">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Sales Invoices
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-navy-900">{{ $invoice->number }}</h1>
                @php
                    $badge = match ($invoice->status) {
                        'paid' => 'bg-success-soft text-success',
                        'partial' => 'bg-warning-soft text-warning',
                        'void' => 'bg-danger-soft text-danger',
                        default => 'bg-surface-muted text-gray-600',
                    };
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">{{ ucfirst($invoice->status) }}</span>
            </div>
            <p class="mt-1 text-lg text-gray-500">Customer: {{ $invoice->customer?->name }}</p>
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

    <div class="grid grid-cols-2 gap-4 rounded-lg border border-surface-outline/30 bg-white p-4 shadow-sm lg:grid-cols-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Invoice Date</p>
            <p class="mt-1 text-lg font-semibold text-navy-900">{{ $invoice->invoice_date?->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Due Date</p>
            <p class="mt-1 text-lg font-semibold text-navy-900">{{ $invoice->due_date?->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Sales Order</p>
            <p class="mt-1 text-lg font-semibold text-navy-900">{{ $invoice->salesOrder?->number ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Prepared By</p>
            <div class="mt-1 flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-royal-50 text-xs font-semibold text-royal-700">
                    {{ strtoupper(substr($invoice->creator?->name ?? '?', 0, 2)) }}
                </div>
                <p class="text-sm text-navy-900">{{ $invoice->creator?->name }}</p>
            </div>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Balance</p>
            <p class="mt-1 text-lg font-semibold {{ $invoice->balance() > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($invoice->balance(), 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="flex flex-col gap-6 xl:col-span-8">
            <div class="overflow-hidden rounded-lg border border-surface-outline/30 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-surface-outline/30 bg-surface-muted/50 px-6 py-4">
                    <h2 class="text-lg font-semibold text-navy-900">Invoice Items</h2>
                    <span class="text-xs font-medium text-gray-500">{{ $invoice->items->count() }} item</span>
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
                            @forelse ($invoice->items as $index => $item)
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
                            <span>{{ number_format((float) $invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Discount</span>
                            <span>-{{ number_format((float) $invoice->discount_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Tax</span>
                            <span>{{ number_format((float) $invoice->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-surface-outline/50 pt-2 text-lg font-bold text-navy-900">
                            <span>Total</span>
                            <span>{{ number_format((float) $invoice->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Paid</span>
                            <span class="text-success">-{{ number_format((float) $invoice->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold {{ $invoice->balance() > 0 ? 'text-warning' : 'text-success' }}">
                            <span>Balance</span>
                            <span>{{ number_format($invoice->balance(), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($invoice->notes)
                <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-navy-900">Notes</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $invoice->notes }}</p>
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
                        <p class="mt-1 font-medium text-navy-900">{{ $invoice->customer?->name }}</p>
                        @if ($invoice->customer?->address)
                            <p class="mt-1 whitespace-pre-line text-gray-500">{{ $invoice->customer->address }}</p>
                        @endif
                    </div>
                    @if ($invoice->customer?->pic_name)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Contact Person</p>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-royal-50 text-xs font-semibold text-royal-700">
                                    {{ strtoupper(substr($invoice->customer->pic_name, 0, 2)) }}
                                </div>
                                <p class="font-medium text-navy-900">{{ $invoice->customer->pic_name }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex flex-col gap-2 pt-1">
                        @if ($invoice->customer?->email)
                            <div class="flex items-center gap-2 text-navy-800">
                                <span class="material-symbols-outlined text-[16px] text-gray-500">mail</span>
                                {{ $invoice->customer->email }}
                            </div>
                        @endif
                        @if ($invoice->customer?->phone)
                            <div class="flex items-center gap-2 text-navy-800">
                                <span class="material-symbols-outlined text-[16px] text-gray-500">phone</span>
                                {{ $invoice->customer->phone }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-surface-outline/30 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-surface-outline/30 bg-surface-muted/50 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[24px] text-royal-600">payments</span>
                        <h3 class="text-base font-semibold text-navy-900">Payment History</h3>
                    </div>
                    <span class="text-xs font-medium text-gray-500">{{ $invoice->payments->count() }} transaksi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm text-navy-900">
                        <thead>
                            <tr class="border-b border-surface-outline/30 bg-surface-muted/30 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="p-3">No.</th>
                                <th class="p-3">Nomor</th>
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Metode</th>
                                <th class="p-3">Referensi</th>
                                <th class="p-3">Jumlah</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-outline/20">
                            @forelse ($invoice->payments as $index => $payment)
                                <tr class="transition-colors hover:bg-surface-muted/50">
                                    <td class="p-3 text-gray-500">{{ $index + 1 }}</td>
                                    <td class="p-3 font-medium text-navy-900">{{ $payment->number }}</td>
                                    <td class="p-3 text-gray-600">{{ $payment->payment_date?->format('d M Y') }}</td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center gap-1.5 capitalize text-gray-700">
                                            <span class="material-symbols-outlined text-[16px] text-gray-400">
                                                {{ $payment->payment_method === 'cash' ? 'payments' : 'account_balance' }}
                                            </span>
                                            {{ str_replace('_', ' ', $payment->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-600">{{ $payment->reference ?? '—' }}</td>
                                    <td class="p-3 font-semibold text-success">{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td class="p-3">
                                        @if ($payment->status === 'void')
                                            <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs font-semibold text-danger">Void</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-success-soft px-2.5 py-0.5 text-xs font-semibold text-success">Posted</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-gray-600">{{ $payment->creator?->name }}</td>
                                </tr>
                                @if ($payment->notes)
                                    <tr class="bg-surface-muted/20">
                                        <td colspan="8" class="px-3 pb-3 text-xs text-gray-500">
                                            <span class="font-semibold text-gray-400">Notes:</span> {{ $payment->notes }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="8" class="p-6 text-center text-sm text-gray-500">Belum ada pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end border-t border-surface-outline/30 bg-surface-muted/20 px-5 py-3">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500">Total Dibayar</span>
                        <span class="font-bold text-success">{{ number_format((float) $invoice->payments->sum('amount'), 2) }}</span>
                    </div>
                </div>
            </div>

            @if ($invoice->salesOrder)
                <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-2 border-b border-surface-outline/30 pb-3">
                        <span class="material-symbols-outlined text-[24px] text-royal-600">receipt_long</span>
                        <h3 class="text-base font-semibold text-navy-900">Sales Order</h3>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-navy-900">{{ $invoice->salesOrder->number }}</p>
                        <a href="{{ route('sales.orders.show', $invoice->salesOrder) }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-medium text-royal hover:underline">
                            Detail
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>

    {{-- ==== Laser / A4 print area ==== --}}
    <div class="print-area print-laser">
        @include('pdf.partials.laser-sales-invoice', ['invoice' => $invoice])
    </div>

    {{-- ==== Dot Matrix print area (9.5x11 continuous) ==== --}}
    <div class="print-area print-matrix">
        <div class="mx-center mx-brand">{{ \App\Support\CompanyProfile::name() }}</div>
        <div class="mx-center mx-title">Sales Invoice</div>
        <div class="mx-center" style="font-weight:700;">{{ $invoice->number }}</div>
        <div class="mx-center" style="margin-bottom:4px;">{{ $invoice->invoice_date?->format('d M Y') }} • Due {{ $invoice->due_date?->format('d M Y') }}</div>

        <div class="mx-row"><span class="k">Bill To</span><span>{{ $invoice->customer?->name }}</span></div>
        <div class="mx-row"><span class="k">SO</span><span>{{ $invoice->salesOrder?->number }}</span></div>

        <div class="mx-items">
            <div class="mx-row hd">
                <span class="num">#</span>
                <span class="desc">Item</span>
                <span class="rt">Qty</span>
                <span class="rt">Price</span>
                <span class="rt">Total</span>
            </div>
            @forelse ($invoice->items as $index => $item)
                <div class="mx-row">
                    <span class="num">{{ $index + 1 }}</span>
                    <span class="desc">{{ $item->product?->name ?: $item->description }}</span>
                    <span class="rt">{{ number_format((float) $item->qty, 2) }}</span>
                    <span class="rt">{{ number_format((float) $item->unit_price, 2) }}</span>
                    <span class="rt" style="font-weight:700;">{{ number_format((float) $item->line_total, 2) }}</span>
                </div>
            @empty
                <div class="mx-row"><span>No items.</span></div>
            @endforelse
        </div>

        <div class="mx-sum">
            <div class="mx-row"><span>Subtotal</span><span>{{ number_format((float) $invoice->subtotal, 2) }}</span></div>
            <div class="mx-row"><span>Discount</span><span>-{{ number_format((float) $invoice->discount_amount, 2) }}</span></div>
            <div class="mx-row"><span>Tax</span><span>{{ number_format((float) $invoice->tax_amount, 2) }}</span></div>
            <div class="mx-row" style="font-weight:700;"><span>Total</span><span>{{ number_format((float) $invoice->total, 2) }}</span></div>
            <div class="mx-row"><span>Paid</span><span>-{{ number_format((float) $invoice->paid_amount, 2) }}</span></div>
            <div class="mx-row" style="font-weight:700;"><span>Balance</span><span>{{ number_format($invoice->balance(), 2) }}</span></div>
        </div>

        @if ($invoice->notes)
            <div class="mx-row" style="margin-top:2px;"><span class="k">Notes</span></div>
            <div>{{ $invoice->notes }}</div>
        @endif

        <div class="mx-sign">
            <div class="col">
                <div class="lbl">Prepared By</div>
                <div class="line"></div>
                <div class="mx-center">{{ $invoice->creator?->name }}</div>
            </div>
            <div class="col">
                <div class="lbl">Approved By</div>
                <div class="line"></div>
                <div class="mx-center">Management</div>
            </div>
        </div>

        <div class="mx-center mx-footer">{{ now()->format('d M Y H:i') }}</div>
    </div>
</div>
