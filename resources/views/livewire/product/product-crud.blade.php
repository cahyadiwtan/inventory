<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Product</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola data produk, stok, barcode &amp; harga</p>
        </div>
        <div class="flex items-center gap-3">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari kode / nama / barcode..."
                class="input-field !w-64"
            >
            <button wire:click="openCreate" class="btn-primary">
                + Tambah
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Kode</th>
                    <th class="table-header">Nama</th>
                    <th class="table-header">Kategori</th>
                    <th class="table-header">Unit</th>
                    <th class="table-header text-right">Harga Jual</th>
                    <th class="table-header text-right">Stok</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($items as $item)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $item->code }}</td>
                        <td class="px-6 py-4 text-sm text-navy-900">{{ $item->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $item->category?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $item->unit?->symbol }}</td>
                        <td class="px-6 py-4 text-right text-sm text-navy-900">{{ number_format($item->selling_price, 2) }}</td>
                        <td class="px-6 py-4 text-right text-sm text-navy-900">{{ number_format($item->warehouses->sum('qty_on_hand'), 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <button
                                wire:click="toggleActive('{{ $item->id }}')"
                                class="rounded-md px-3 py-1 text-xs font-semibold {{ $item->is_active ? 'badge-success' : 'bg-gray-100 text-gray-500' }}"
                            >
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <button wire:click="openEdit('{{ $item->id }}')" class="font-medium text-royal hover:text-royal-600">Edit</button>
                            <button wire:click="delete('{{ $item->id }}')" wire:confirm="Yakin hapus?" class="ml-3 font-medium text-danger hover:text-danger/80">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $items->links() }}</div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-navy-900/50 py-8" wire:click.self="resetForm">
            <div class="card w-full max-w-2xl p-6 shadow-dropdown">
                <h2 class="text-lg font-semibold text-navy-900">
                    {{ $editingId ? 'Edit' : 'Tambah' }} Product
                </h2>

                <form wire:submit="save" class="mt-4 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Kode <span class="text-danger">*</span></label>
                            <input type="text" wire:model="form.code" class="input-field mt-1">
                            @error('form.code')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Barcode</label>
                            <input type="text" wire:model="form.barcode" class="input-field mt-1">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama <span class="text-danger">*</span></label>
                        <input type="text" wire:model="form.name" class="input-field mt-1">
                        @error('form.name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Kategori <span class="text-danger">*</span></label>
                            <select wire:model="form.category_id" class="input-field mt-1">
                                <option value="">-- Pilih --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('form.category_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Merek</label>
                            <select wire:model="form.brand_id" class="input-field mt-1">
                                <option value="">-- Pilih --</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Unit <span class="text-danger">*</span></label>
                            <select wire:model="form.unit_id" class="input-field mt-1">
                                <option value="">-- Pilih --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                                @endforeach
                            </select>
                            @error('form.unit_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Harga Jual <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" wire:model="form.selling_price" class="input-field mt-1">
                            @error('form.selling_price')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Harga Beli <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" wire:model="form.purchase_price" class="input-field mt-1">
                            @error('form.purchase_price')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Min Stock</label>
                            <input type="number" step="0.01" wire:model="form.min_stock" class="input-field mt-1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Max Stock</label>
                            <input type="number" step="0.01" wire:model="form.max_stock" class="input-field mt-1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Reorder Point</label>
                            <input type="number" step="0.01" wire:model="form.reorder_point" class="input-field mt-1">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Berat (kg)</label>
                            <input type="number" step="0.001" wire:model="form.weight" class="input-field mt-1">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-gray-700">Barcode Tambahan</label>
                            <button type="button" wire:click="addBarcode" class="text-sm font-medium text-royal hover:text-royal-600">+ Tambah Barcode</button>
                        </div>
                        @foreach ($barcodes as $index => $barcode)
                            <div class="mt-2 flex items-center gap-2">
                                <input type="text" wire:model="barcodes.{{ $index }}" placeholder="Barcode {{ $index + 1 }}" class="input-field">
                                <button type="button" wire:click="removeBarcode({{ $index }})" class="rounded-lg p-2 text-danger hover:bg-danger-soft">X</button>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-gray-700">Harga Spesial</label>
                            <button type="button" wire:click="addPrice" class="text-sm font-medium text-royal hover:text-royal-600">+ Tambah Harga</button>
                        </div>
                        @foreach ($prices as $index => $price)
                            <div class="mt-2 grid grid-cols-[1fr_1fr_1fr_auto] items-center gap-2">
                                <select wire:model="prices.{{ $index }}.price_type" class="input-field">
                                    <option value="selling">Selling</option>
                                    <option value="purchase">Purchase</option>
                                    <option value="special">Special</option>
                                </select>
                                <input type="number" step="0.01" wire:model="prices.{{ $index }}.price" placeholder="Harga" class="input-field">
                                <input type="date" wire:model="prices.{{ $index }}.valid_from" class="input-field">
                                <button type="button" wire:click="removePrice({{ $index }})" class="rounded-lg p-2 text-danger hover:bg-danger-soft">X</button>
                            </div>
                            <div class="mt-1">
                                <input type="date" wire:model="prices.{{ $index }}.valid_to" placeholder="Berlaku sampai" class="input-field">
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Stok per Gudang</label>
                        @foreach ($warehouses as $warehouse)
                            <div class="mt-2 flex items-center gap-3">
                                <span class="w-40 text-sm text-gray-600">{{ $warehouse->name }}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="stocks.{{ $warehouse->id }}"
                                    class="input-field"
                                >
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="resetForm" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
