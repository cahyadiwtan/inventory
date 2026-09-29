<div class="flex flex-col gap-6" x-data="{
    productPrices: @js($productPrices),
    taxRates: @js($taxes->pluck('rate', 'id')),
    totals: { subtotal: {{ $subtotal }}, discountTotal: {{ $discountTotal }}, taxTotal: {{ $taxTotal }}, grandTotal: {{ $grandTotal }} },
    fmt(value) {
        return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    recompute() {
        let subtotal = 0, discount = 0, tax = 0, grand = 0;
        this.$root.querySelectorAll('tr[data-line]').forEach((tr) => {
            const qty = parseFloat(tr.querySelector('[data-qty]')?.value) || 0;
            const price = parseFloat(tr.querySelector('[data-unit-price]')?.value) || 0;
            const discPct = parseFloat(tr.querySelector('[data-discount]')?.value) || 0;
            const rate = this.taxRates[tr.querySelector('[data-tax]')?.value] ?? 0;
            const lineSub = qty * price;
            const lineDisc = lineSub * discPct / 100;
            const base = lineSub - lineDisc;
            const lineTax = base * rate / 100;
            const lineTotal = Math.round((base + lineTax) * 100) / 100;
            subtotal += lineSub;
            discount += lineDisc;
            tax += lineTax;
            grand += lineTotal;
            const cell = tr.querySelector('[data-line-total]');
            if (cell) cell.textContent = this.fmt(lineTotal);
        });
        this.totals.subtotal = subtotal;
        this.totals.discountTotal = discount;
        this.totals.taxTotal = tax;
        this.totals.grandTotal = grand;
    },
}">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Direct Invoice</h1>
            <p class="mt-1 text-sm text-gray-500">Buat invoice langsung (tanpa quotation / SO / DO) — otomatis menghasilkan invoice + surat jalan dan stok keluar.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col gap-6 xl:flex-row">
        <div class="card min-w-0 flex-1 p-6">
            <div class="flex items-center gap-3 border-b border-surface-outline/30 pb-4">
                <h2 class="text-lg font-semibold text-navy-900">Buat Direct Invoice</h2>
                <span class="rounded bg-surface-dim px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-gray-600">Langsung Posted</span>
            </div>

            <form wire:submit="create" class="mt-6 flex flex-col gap-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Customer <span class="text-danger">*</span></label>
                        <select wire:model="customerId" class="input-field mt-1">
                            <option value="">-- Pilih Customer --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        @error('customerId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Gudang (stok keluar) <span class="text-danger">*</span></label>
                        <select wire:model="warehouseId" class="input-field mt-1">
                            <option value="">-- Pilih Gudang --</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouseId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal Invoice <span class="text-danger">*</span></label>
                        <input type="date" wire:model="invoiceDate" class="input-field mt-1">
                        @error('invoiceDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Jatuh Tempo <span class="text-danger">*</span></label>
                        <input type="date" wire:model="dueDate" class="input-field mt-1">
                        @error('dueDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal Kirim <span class="text-danger">*</span></label>
                        <input type="date" wire:model="deliveryDate" class="input-field mt-1">
                        @error('deliveryDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Catatan</label>
                        <input type="text" wire:model="notes" placeholder="Opsional" class="input-field mt-1">
                        @error('notes')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-surface-outline/30">
                    <div class="flex items-center justify-between bg-surface-muted/50 px-4 py-3">
                        <h3 class="text-sm font-semibold text-navy-900">Item</h3>
                        <button type="button" wire:click="addItem" class="flex h-8 items-center gap-1 rounded-md border border-dashed border-surface-outline px-3 text-xs font-medium text-gray-600 transition-colors hover:border-royal/50 hover:text-royal">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            Tambah Item
                        </button>
                    </div>

                    @error('items')<p class="px-4 pt-2 text-xs text-danger">{{ $message }}</p>@enderror

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] border-collapse text-left text-sm text-navy-900">
                            <thead>
                                <tr class="border-y border-surface-outline/30 bg-surface-muted/50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th class="w-10 p-3 text-center">#</th>
                                    <th class="p-3">Produk</th>
                                    <th class="p-3 text-right">Qty</th>
                                    <th class="p-3 text-right">Harga</th>
                                    <th class="p-3 text-right">Disc. %</th>
                                    <th class="p-3 text-right">Pajak</th>
                                    <th class="p-3 text-right">Total</th>
                                    <th class="w-10 p-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-outline/20">
                                @forelse ($items as $index => $item)
                                    <tr class="group transition-colors hover:bg-surface-muted/50" data-line>
                                        <td class="p-3 text-center text-gray-500">{{ $index + 1 }}</td>
                                        <td class="p-3">
                                            <select
                                                wire:model="items.{{ $index }}.product_id"
                                                @change="const priceInput = $event.target.closest('tr').querySelector('[data-unit-price]'); if (priceInput) { priceInput.value = productPrices[$event.target.value] ?? 0; priceInput.dispatchEvent(new Event('input', { bubbles: true })); } recompute()"
                                                class="input-field !py-1.5">
                                                <option value="">-- Produk --</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                            @error("items.{$index}.product_id")<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                                            @error("items.{$index}.qty")<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                                        </td>
                                        <td class="p-3">
                                            <input type="number" step="0.01" min="0" data-qty wire:model.defer="items.{{ $index }}.qty" @input="recompute()" class="input-field !py-1.5 text-right">
                                        </td>
                                        <td class="p-3">
                                            <input type="number" step="0.01" min="0" data-unit-price wire:model.defer="items.{{ $index }}.unit_price" @input="recompute()" class="input-field !py-1.5 text-right">
                                        </td>
                                        <td class="p-3">
                                            <input type="number" step="0.01" min="0" max="100" data-discount wire:model.defer="items.{{ $index }}.discount" @input="recompute()" class="input-field !py-1.5 text-right">
                                        </td>
                                        <td class="p-3">
                                            <select data-tax wire:model.defer="items.{{ $index }}.tax_id" @change="recompute()" class="input-field !py-1.5 text-right">
                                                <option value="">—</option>
                                                @foreach ($taxes as $tax)
                                                    <option value="{{ $tax->id }}">{{ $tax->rate }}%</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-3 text-right font-medium" data-line-total>{{ number_format($sales->lineTotal((float) $item['qty'], (float) $item['unit_price'], (float) ($item['discount'] ?? 0), $item['tax_id'] ? $taxes->firstWhere('id', $item['tax_id']) : null), 2) }}</td>
                                        <td class="p-3 text-center">
                                            <button type="button" wire:click="removeItem({{ $index }})" class="rounded p-1 text-gray-400 opacity-0 transition-all hover:text-danger group-hover:opacity-100">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-8 text-center text-sm text-gray-500">
                                            Belum ada item. Klik "Tambah Item".
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-4 border-t border-surface-outline/30 bg-surface-muted/20 p-4">
                        <div class="w-full max-w-sm space-y-1.5 text-sm">
                            <div class="flex justify-between text-gray-500"><span>Subtotal</span><span x-text="fmt(totals.subtotal)"></span></div>
                            <div class="flex justify-between text-danger"><span>Diskon</span><span><span x-text="'-' + fmt(totals.discountTotal)"></span></span></div>
                            <div class="flex justify-between text-gray-500"><span>Pajak</span><span x-text="fmt(totals.taxTotal)"></span></div>
                            <div class="flex justify-between border-t border-surface-outline/50 pt-2 text-lg font-bold text-navy-900"><span>Total</span><span x-text="fmt(totals.grandTotal)"></span></div>
                        </div>
                        <button type="submit" class="btn-primary">Buat Invoice + Surat Jalan</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="flex w-full flex-col gap-6 xl:w-80 xl:shrink-0">
            <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Catatan</h3>
                <p class="mt-2 text-sm text-gray-600">Saat disimpan: invoice (DINV) + surat jalan (DO) langsung berstatus <b>posted</b>, dan stok gudang terpilih langsung berkurang.</p>
            </div>

            @if ($stockLevels)
                <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Stok</h3>
                    <div class="mt-4 space-y-4">
                        @foreach ($stockLevels as $level)
                            <div>
                                <div class="flex justify-between text-xs font-medium text-navy-900">
                                    <span class="truncate pr-2">{{ $level['name'] }}</span>
                                    <span class="shrink-0 {{ $level['stock'] >= $level['qty'] ? 'text-royal-600' : 'text-danger' }}">{{ number_format($level['stock'], 0) }} stok</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-surface-muted">
                                    <div class="h-full rounded-full {{ $level['stock'] >= $level['qty'] ? 'bg-royal' : 'bg-danger' }}" style="width: {{ min(100, $level['stock'] > 0 ? ($level['qty'] / $level['stock']) * 100 : 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Invoice</th>
                    <th class="table-header">Surat Jalan</th>
                    <th class="table-header">Customer</th>
                    <th class="table-header">Tanggal</th>
                    <th class="table-header text-right">Total</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $invoice->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->deliveryOrder?->number ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->customer?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->invoice_date?->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-navy-900">{{ number_format((float) $invoice->total, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'partial' ? 'badge-warning' : 'bg-gray-100 text-gray-500') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('sales.direct-invoices.show', $invoice) }}" wire:navigate class="font-medium text-royal hover:text-royal/80">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada direct invoice.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>