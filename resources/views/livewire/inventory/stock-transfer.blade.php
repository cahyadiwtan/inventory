<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Stock Transfer</h1>
            <p class="mt-1 text-sm text-gray-500">Transfer stok antar gudang (request → approve → transfer → receive)</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-navy-900">Buat Transfer</h2>

        <form wire:submit="request" class="mt-4 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Dari Gudang <span class="text-danger">*</span></label>
                    <select wire:model="fromWarehouseId" class="input-field mt-1">
                        <option value="">-- Pilih --</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('fromWarehouseId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Ke Gudang <span class="text-danger">*</span></label>
                    <select wire:model="toWarehouseId" class="input-field mt-1">
                        <option value="">-- Pilih --</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('toWarehouseId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
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
                    <div class="mt-2 grid grid-cols-[1fr_1fr_auto] items-center gap-2">
                        <select wire:model="items.{{ $index }}.product_id" class="input-field">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" wire:model="items.{{ $index }}.qty" placeholder="Qty" class="input-field">
                        <button type="button" wire:click="removeItem({{ $index }})" class="rounded-lg p-2 text-danger hover:bg-danger-soft">X</button>
                    </div>
                    @error("items.{$index}.product_id")<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    @error("items.{$index}.qty")<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                @endforeach
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="btn-primary">Request Transfer</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Nomor</th>
                    <th class="table-header">Asal</th>
                    <th class="table-header">Tujuan</th>
                    <th class="table-header">Item</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($transfers as $transfer)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $transfer->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $transfer->fromWarehouse?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $transfer->toWarehouse?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @foreach ($transfer->items as $item)
                                {{ $item->product?->name }} ({{ $item->qty }})
                                @if (! $loop->last), @endif
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold
                                {{ $transfer->status === 'received' ? 'badge-success' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($transfer->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if ($transfer->status === 'requested')
                                <button wire:click="approve('{{ $transfer->id }}')" class="font-medium text-success hover:text-success/80">Approve</button>
                                <button wire:click="reject('{{ $transfer->id }}')" wire:confirm="Yakin reject?" class="ml-3 font-medium text-danger hover:text-danger/80">Reject</button>
                            @elseif ($transfer->status === 'approved')
                                <button wire:click="transfer('{{ $transfer->id }}')" wire:confirm="Post stok keluar?" class="font-medium text-royal hover:text-royal-600">Transfer</button>
                            @elseif ($transfer->status === 'transferred')
                                <button wire:click="receive('{{ $transfer->id }}')" wire:confirm="Terima stok masuk?" class="font-medium text-success hover:text-success/80">Receive</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada transfer.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
