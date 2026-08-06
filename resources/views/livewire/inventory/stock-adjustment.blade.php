<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Stock Adjustment</h1>
            <p class="mt-1 text-sm text-gray-500">Penyesuaian stok (plus / minus) dengan alasan</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-navy-900">Buat Adjustment</h2>

        <form wire:submit="post" class="mt-4 space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tipe <span class="text-danger">*</span></label>
                    <select wire:model="type" class="input-field mt-1">
                        <option value="plus">Plus (+)</option>
                        <option value="minus">Minus (-)</option>
                    </select>
                    @error('type')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
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
                    <label class="block text-sm font-semibold text-gray-700">Alasan <span class="text-danger">*</span></label>
                    <input type="text" wire:model="reason" placeholder="mis. rusak / hilang / koreksi" class="input-field mt-1">
                    @error('reason')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
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
                <button type="submit" class="btn-primary">Post Adjustment</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Nomor</th>
                    <th class="table-header">Gudang</th>
                    <th class="table-header">Tipe</th>
                    <th class="table-header">Alasan</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($adjustments as $adjustment)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $adjustment->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $adjustment->warehouse?->name }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold {{ $adjustment->type === 'plus' ? 'badge-success' : 'badge-danger' }}">
                                {{ strtoupper($adjustment->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $adjustment->reason }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold {{ $adjustment->isPosted() ? 'badge-success' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($adjustment->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if (! $adjustment->isPosted())
                                <button wire:click="delete('{{ $adjustment->id }}')" wire:confirm="Yakin hapus?" class="font-medium text-danger hover:text-danger/80">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada adjustment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
