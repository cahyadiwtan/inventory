<div class="flex flex-col gap-6" x-data="{
    productPrices: @js($productPrices),
    taxRates: @js($taxes->pluck('rate', 'id')),
    customerMap: @js($customerMap),
    selectedCustomer: @js($selectedCustomer),
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
    <div class="sticky top-16 z-20 -mx-4 flex items-center justify-between border-b border-surface-outline/30 bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="flex items-center gap-3">
            <a href="{{ ($editing ?? false) ? route('sales.quotations.show', $editingQuotation) : route('sales.quotations.index') }}" wire:navigate class="rounded-full p-2 text-gray-500 transition-colors hover:bg-surface-muted">
                <span class="material-symbols-outlined text-[24px]">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-navy-900">{{ ($editing ?? false) ? 'Edit Quotation' : 'New Quotation' }}</h1>
                    @if ($editing ?? false)
                        @php
                            $badge = match ($editingQuotation->status) {
                                'draft' => 'bg-surface-dim text-gray-600',
                                'sent' => 'bg-royal-50 text-royal-700',
                                default => 'bg-surface-dim text-gray-600',
                            };
                        @endphp
                        <span class="rounded px-2 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $badge }}">{{ ucfirst($editingQuotation->status) }}</span>
                    @else
                        <span class="rounded bg-surface-dim px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-gray-600">Draft</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500">{{ ($editing ?? false) ? $editingQuotation->number : 'Unsaved' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="cancel" class="hidden h-10 items-center rounded-lg px-4 text-sm font-semibold text-gray-600 transition-colors hover:bg-surface-muted sm:flex">Cancel</button>
            <button type="button" wire:click="save" class="h-10 rounded-lg border border-surface-outline bg-white px-4 text-sm font-semibold text-navy-800 shadow-sm transition-colors hover:bg-surface-muted">{{ ($editing ?? false) ? 'Save Changes' : 'Save as Draft' }}</button>
            <button type="button" wire:click="saveAndSend" class="flex h-10 items-center gap-2 rounded-lg bg-royal px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-royal-600">
                <span class="material-symbols-outlined text-[18px]">send</span>
                {{ ($editing ?? false) ? 'Save & Send' : 'Send to Customer' }}
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col gap-6 xl:flex-row">
        <div class="flex min-w-0 flex-1 flex-col gap-6">
            <div class="rounded-lg border border-surface-outline/30 bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-lg font-semibold text-navy-900">
                    <span class="material-symbols-outlined text-[20px] text-royal-600">info</span>
                    Quotation Details
                </h2>

                <form wire:submit="save" class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Customer <span class="text-danger">*</span></label>
                        <select wire:model="customerId" @change="selectedCustomer = customerMap[$event.target.value] ?? null" class="input-field mt-1">
                            <option value="">-- Pilih Customer --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        @error('customerId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Quotation Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="quotationDate" class="input-field mt-1">
                        @error('quotationDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Valid Until <span class="text-danger">*</span></label>
                        <input type="date" wire:model="validUntil" class="input-field mt-1">
                        @error('validUntil')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Notes / Terms</label>
                        <textarea wire:model="notes" rows="1" placeholder="Opsional" class="input-field mt-1"></textarea>
                        @error('notes')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                </form>
            </div>

            <div class="flex flex-col overflow-hidden rounded-lg border border-surface-outline/30 bg-white shadow-sm">
                <div class="flex items-center justify-between p-6 pb-4">
                    <h2 class="flex items-center gap-2 text-lg font-semibold text-navy-900">
                        <span class="material-symbols-outlined text-[20px] text-royal-600">list_alt</span>
                        Line Items
                    </h2>
                    <button type="button" wire:click="addItem" class="flex h-9 items-center gap-1 rounded-lg border border-dashed border-surface-outline px-3 text-sm font-medium text-gray-600 transition-colors hover:border-royal/50 hover:text-royal">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Add Line Item
                    </button>
                </div>

                @error('items')<p class="px-6 pb-2 text-xs text-danger">{{ $message }}</p>@enderror

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] border-collapse text-left text-sm text-navy-900">
                        <thead>
                            <tr class="border-y border-surface-outline/30 bg-surface-muted/50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="w-12 p-4 text-center">#</th>
                                <th class="p-4">Product / SKU</th>
                                <th class="p-4 text-right">Qty</th>
                                <th class="p-4 text-right">Unit Price</th>
                                <th class="p-4 text-right">Disc. %</th>
                                <th class="p-4 text-right">Tax %</th>
                                <th class="p-4 text-right">Total</th>
                                <th class="w-12 p-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-outline/20">
                            @forelse ($items as $index => $item)
                                <tr class="group transition-colors hover:bg-surface-muted/50" data-line>
                                    <td class="p-4 text-center text-gray-500">{{ $index + 1 }}</td>
                                    <td class="p-4">
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
                                    <td class="p-4">
                                        <input type="number" step="0.01" min="0" data-qty wire:model.defer="items.{{ $index }}.qty" @input="recompute()" class="input-field !py-1.5 text-right">
                                    </td>
                                    <td class="p-4">
                                        <input type="number" step="0.01" min="0" data-unit-price wire:model.defer="items.{{ $index }}.unit_price" @input="recompute()" class="input-field !py-1.5 text-right">
                                    </td>
                                    <td class="p-4">
                                        <input type="number" step="0.01" min="0" max="100" data-discount wire:model.defer="items.{{ $index }}.discount" @input="recompute()" class="input-field !py-1.5 text-right">
                                    </td>
                                    <td class="p-4">
                                        <select data-tax wire:model.defer="items.{{ $index }}.tax_id" @change="recompute()" class="input-field !py-1.5 text-right">
                                            <option value="">—</option>
                                            @foreach ($taxes as $tax)
                                                <option value="{{ $tax->id }}">{{ $tax->rate }}%</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-4 text-right font-medium" data-line-total>{{ number_format($sales->lineTotal((float) $item['qty'], (float) $item['unit_price'], (float) ($item['discount'] ?? 0), $item['tax_id'] ? $taxes->firstWhere('id', $item['tax_id']) : null), 2) }}</td>
                                    <td class="p-4 text-center">
                                        <button type="button" wire:click="removeItem({{ $index }})" class="rounded p-1 text-gray-400 opacity-0 transition-all hover:text-danger group-hover:opacity-100">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-sm text-gray-500">
                                        Belum ada item. Klik "Add Line Item" untuk menambahkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end border-t border-surface-outline/30 bg-surface-muted/20 p-6">
                    <div class="w-full max-w-sm space-y-2 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span x-text="fmt(totals.subtotal)"></span>
                        </div>
                        <div class="flex justify-between text-danger">
                            <span>Discount Total</span>
                            <span><span x-text="'-' + fmt(totals.discountTotal)"></span></span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Tax</span>
                            <span x-text="fmt(totals.taxTotal)"></span>
                        </div>
                        <div class="flex justify-between border-t border-surface-outline/50 pt-2 text-lg font-bold text-navy-900">
                            <span>Grand Total</span>
                            <span x-text="fmt(totals.grandTotal)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex w-full flex-col gap-6 xl:w-80 xl:shrink-0">
            <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                <h3 class="flex items-center gap-2 border-b border-surface-outline/30 pb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <span class="material-symbols-outlined text-[16px]">business</span>
                    Customer Snapshot
                </h3>
                <template x-if="selectedCustomer">
                    <div class="mt-4">
                        <div class="text-lg font-bold text-navy-900" x-text="selectedCustomer.name"></div>
                        <p x-show="selectedCustomer.address" class="mt-1 whitespace-pre-line text-sm text-gray-500" x-text="selectedCustomer.address"></p>
                    </div>
                    <div class="mt-3 space-y-3 rounded-lg bg-surface-muted/50 p-3 text-sm">
                        <template x-if="selectedCustomer.pic_name">
                            <div>
                                <div class="text-xs text-gray-500">Primary Contact</div>
                                <div class="mt-0.5 flex items-center gap-2 font-medium text-navy-900">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-royal-50 text-xs font-semibold text-royal-700">
                                        <span x-text="selectedCustomer.pic_name.substring(0, 2).toUpperCase()"></span>
                                    </div>
                                    <span x-text="selectedCustomer.pic_name"></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="selectedCustomer.email" class="text-sm text-gray-600" x-text="selectedCustomer.email"></div>
                        <div x-show="selectedCustomer.phone" class="text-sm text-gray-600" x-text="selectedCustomer.phone"></div>
                    </div>
                </template>
                <p x-show="!selectedCustomer" class="mt-4 text-sm text-gray-500">Pilih customer untuk melihat ringkasan.</p>
            </div>

            @if ($stockLevels)
                <div class="rounded-lg border border-surface-outline/30 bg-white p-5 shadow-sm">
                    <h3 class="flex items-center gap-2 border-b border-surface-outline/30 pb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <span class="material-symbols-outlined text-[16px]">inventory_2</span>
                        Stock Check
                    </h3>
                    <div class="mt-4 space-y-4">
                        @foreach ($stockLevels as $level)
                            <div>
                                <div class="flex justify-between text-xs font-medium text-navy-900">
                                    <span class="truncate pr-2">{{ $level['name'] }}</span>
                                    <span class="shrink-0 {{ $level['stock'] >= $level['qty'] ? 'text-royal-600' : 'text-danger' }}">
                                        {{ number_format($level['stock'], 0) }} available
                                    </span>
                                </div>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-surface-muted">
                                    <div class="h-full rounded-full {{ $level['stock'] >= $level['qty'] ? 'bg-royal' : 'bg-danger' }}" style="width: {{ min(100, $level['stock'] > 0 ? ($level['qty'] / $level['stock']) * 100 : 100) }}%"></div>
                                </div>
                                <div class="mt-0.5 text-right text-[10px] text-gray-400">Req: {{ number_format($level['qty'], 0) }} / Stock: {{ number_format($level['stock'], 0) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
