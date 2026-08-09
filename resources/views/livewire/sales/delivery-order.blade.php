<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Delivery Order</h1>
            <p class="mt-1 text-sm text-gray-500">Picking dari sales order ter-approve → posting (stok keluar)</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-navy-900">Buat Delivery Order</h2>

        <form wire:submit="create" class="mt-4 space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Sales Order <span class="text-danger">*</span></label>
                    <select wire:model.live="salesOrderId" class="input-field mt-1">
                        <option value="">-- Pilih SO --</option>
                        @foreach ($openOrders as $order)
                            <option value="{{ $order->id }}">{{ $order->number }} - {{ $order->customer?->name }}</option>
                        @endforeach
                    </select>
                    @error('salesOrderId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Gudang <span class="text-danger">*</span></label>
                    <select wire:model="warehouseId" class="input-field mt-1">
                        <option value="">-- Pilih --</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouseId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tanggal Kirim <span class="text-danger">*</span></label>
                    <input type="date" wire:model="deliveryDate" class="input-field mt-1">
                    @error('deliveryDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Catatan</label>
                <input type="text" wire:model="notes" placeholder="Opsional" class="input-field mt-1">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Item (qty = sisa belum dikirim)</label>
                @error('items')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror

                @forelse ($items as $index => $item)
                    <div class="mt-2 grid grid-cols-[1fr_0.7fr_0.7fr_0.7fr] items-center gap-2">
                        <div class="text-sm text-navy-900">{{ $item['product_name'] }}</div>
                        <div class="text-right text-sm text-gray-500">Order: {{ $item['order_qty'] }}</div>
                        <div class="text-right text-sm text-gray-500">Dikirim: {{ $item['delivered'] }}</div>
                        <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.qty" class="input-field">
                    </div>
                    @error("items.{$index}.qty")<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                @empty
                    <p class="mt-2 text-sm text-gray-500">Pilih sales order untuk memuat item.</p>
                @endforelse
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="btn-primary">Buat DO</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Nomor</th>
                    <th class="table-header">Sales Order</th>
                    <th class="table-header">Customer</th>
                    <th class="table-header">Gudang</th>
                    <th class="table-header">Item</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($deliveries as $delivery)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $delivery->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $delivery->salesOrder?->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $delivery->salesOrder?->customer?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $delivery->warehouse?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @foreach ($delivery->items as $item)
                                {{ $item->product?->name }} ({{ $item->qty }})
                                @if (! $loop->last), @endif
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold
                                {{ $delivery->status === 'posted' ? 'badge-success' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($delivery->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('sales.deliveries.show', $delivery) }}" wire:navigate class="font-medium text-royal hover:text-royal-600">Detail</a>
                                @if ($delivery->status === 'draft')
                                    <button wire:click="post('{{ $delivery->id }}')" wire:confirm="Posting stok keluar?" class="font-medium text-royal hover:text-royal-600">Post</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada delivery order.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
