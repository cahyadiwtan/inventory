<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Purchase Order</h1>
            <p class="mt-1 text-sm text-gray-500">Pesanan pembelian (draft → approved → received)</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-navy-900">Buat Purchase Order</h2>

        <form wire:submit="save" class="mt-4 space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Supplier <span class="text-danger">*</span></label>
                    <select wire:model="supplierId" class="input-field mt-1">
                        <option value="">-- Pilih --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplierId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tanggal PO <span class="text-danger">*</span></label>
                    <input type="date" wire:model="orderDate" class="input-field mt-1">
                    @error('orderDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Estimasi Tiba</label>
                    <input type="date" wire:model="expectedDate" class="input-field mt-1">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Catatan</label>
                <input type="text" wire:model="notes" placeholder="Opsional" class="input-field mt-1">
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-gray-700">Item</label>
                    <button type="button" wire:click="addItem" class="text-sm font-medium text-royal hover:text-royal-600">+ Tambah Item</button>
                </div>
                @error('items')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror

                @foreach ($items as $index => $item)
                    <div class="mt-2 grid grid-cols-[1fr_0.6fr_0.6fr_0.5fr_0.8fr_auto] items-center gap-2">
                        <select wire:model="items.{{ $index }}.product_id" class="input-field">
                            <option value="">-- Produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.qty" placeholder="Qty" class="input-field">
                        <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.unit_price" placeholder="Harga" class="input-field">
                        <input type="number" step="0.01" min="0" max="100" wire:model="items.{{ $index }}.discount" placeholder="Disk %" class="input-field">
                        <select wire:model="items.{{ $index }}.tax_id" class="input-field">
                            <option value="">Tanpa Pajak</option>
                            @foreach ($taxes as $tax)
                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                            @endforeach
                        </select>
                        <button type="button" wire:click="removeItem({{ $index }})" class="rounded-lg p-2 text-danger hover:bg-danger-soft">X</button>
                    </div>
                    @error("items.{$index}.product_id")<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    @error("items.{$index}.qty")<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                @endforeach
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan PO</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Nomor</th>
                    <th class="table-header">Supplier</th>
                    <th class="table-header">Tanggal</th>
                    <th class="table-header">Estimasi</th>
                    <th class="table-header">Item</th>
                    <th class="table-header text-right">Total</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($orders as $order)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $order->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->supplier?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->order_date?->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->expected_date?->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @foreach ($order->items as $item)
                                {{ $item->product?->name }} ({{ $item->qty }})
                                @if (! $loop->last), @endif
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-navy-900">
                            {{ number_format((float) $order->total, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold
                                {{ $order->status === 'approved' ? 'badge-success'
                                    : ($order->status === 'rejected' ? 'badge-danger'
                                    : ($order->status === 'received' ? 'badge-success'
                                    : ($order->status === 'cancelled' ? 'badge-danger' : 'bg-gray-100 text-gray-500'))) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if ($order->status === 'draft')
                                <button wire:click="approve('{{ $order->id }}')" class="font-medium text-success hover:text-success/80">Approve</button>
                                <button wire:click="reject('{{ $order->id }}')" wire:confirm="Yakin reject?" class="ml-3 font-medium text-danger hover:text-danger/80">Reject</button>
                            @elseif ($order->status === 'approved')
                                <button wire:click="cancel('{{ $order->id }}')" wire:confirm="Batalkan PO?" class="font-medium text-danger hover:text-danger/80">Cancel</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada purchase order.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
