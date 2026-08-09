<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Purchase Invoice</h1>
            <p class="mt-1 text-sm text-gray-500">Tagihan dari supplier atas barang diterima + pembayaran (partial/multi)</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-navy-900">Buat Invoice</h2>

        <form wire:submit="create" class="mt-4 space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Purchase Order <span class="text-danger">*</span></label>
                    <select wire:model.live="purchaseOrderId" class="input-field mt-1">
                        <option value="">-- Pilih PO --</option>
                        @foreach ($invoicableOrders as $order)
                            <option value="{{ $order->id }}">{{ $order->number }} - {{ $order->supplier?->name }}</option>
                        @endforeach
                    </select>
                    @error('purchaseOrderId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tanggal Invoice <span class="text-danger">*</span></label>
                    <input type="date" wire:model="invoiceDate" class="input-field mt-1">
                    @error('invoiceDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Jatuh Tempo <span class="text-danger">*</span></label>
                    <input type="date" wire:model="dueDate" class="input-field mt-1">
                    @error('dueDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Catatan</label>
                <input type="text" wire:model="notes" placeholder="Opsional" class="input-field mt-1">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Item (qty = jumlah diterima)</label>
                @error('items')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror

                @forelse ($items as $index => $item)
                    <div class="mt-2 grid grid-cols-[1fr_0.7fr_0.8fr_0.8fr] items-center gap-2">
                        <div class="text-sm text-navy-900">{{ $item['product_name'] }}</div>
                        <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.qty" class="input-field">
                        <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.unit_price" class="input-field">
                        <div class="text-right text-sm text-gray-500">Disk {{ $item['discount'] }}%</div>
                    </div>
                    @error("items.{$index}.qty")<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                @empty
                    <p class="mt-2 text-sm text-gray-500">Pilih PO yang sudah ada barang diterima.</p>
                @endforelse
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="btn-primary">Buat Invoice</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Nomor</th>
                    <th class="table-header">Purchase Order</th>
                    <th class="table-header">Supplier</th>
                    <th class="table-header text-right">Total</th>
                    <th class="table-header text-right">Dibayar</th>
                    <th class="table-header text-right">Sisa</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $invoice->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->purchaseOrder?->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->supplier?->name }}</td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-navy-900">{{ number_format((float) $invoice->total, 2) }}</td>
                        <td class="px-6 py-4 text-right text-sm text-gray-500">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right text-sm font-medium {{ $invoice->balance() > 0 ? 'text-warning' : 'text-success' }}">
                            {{ number_format($invoice->balance(), 2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold
                                {{ $invoice->status === 'paid' ? 'badge-success'
                                    : ($invoice->status === 'partial' ? 'badge-warning' : 'bg-gray-100 text-gray-500') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if ($invoice->status === 'draft')
                                <button wire:click="post('{{ $invoice->id }}')" wire:confirm="Posting invoice?" class="font-medium text-royal hover:text-royal-600">Post</button>
                            @elseif ($invoice->isPosted() && $invoice->balance() > 0)
                                <button wire:click="openPayment('{{ $invoice->id }}')" class="font-medium text-success hover:text-success/80">Bayar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada invoice.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($payingInvoiceId)
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-navy-900">Catat Pembayaran</h2>
                <button wire:click="closePayment" class="rounded-lg p-2 text-gray-400 hover:bg-surface-muted hover:text-navy-900">X</button>
            </div>

            <form wire:submit="submitPayment" class="mt-4 grid grid-cols-4 items-end gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tanggal</label>
                    <input type="date" wire:model="paymentDate" class="input-field mt-1">
                    @error('paymentDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Metode</label>
                    <select wire:model="paymentMethod" class="input-field mt-1">
                        @foreach (['cash', 'bank_transfer', 'check', 'credit', 'other'] as $method)
                            <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                        @endforeach
                    </select>
                    @error('paymentMethod')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" wire:model="paymentAmount" class="input-field mt-1">
                    @error('paymentAmount')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Referensi</label>
                    <input type="text" wire:model="paymentReference" placeholder="Opsional" class="input-field mt-1">
                </div>
                <div class="col-span-4 flex justify-end">
                    <button type="submit" class="btn-primary">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    @endif
</div>
