<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Stock Opname</h1>
            <p class="mt-1 text-sm text-gray-500">Pencatatan stok fisik per gudang (submit → approve → post)</p>
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
        <h2 class="text-lg font-semibold text-navy-900">Buat Opname</h2>

        <form wire:submit="submit" class="mt-4 space-y-4">
            <div class="grid grid-cols-3 gap-4">
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
                    <label class="block text-sm font-semibold text-gray-700">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" wire:model="opnameDate" class="input-field mt-1">
                    @error('opnameDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Catatan</label>
                    <input type="text" wire:model="notes" class="input-field mt-1">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="block text-sm font-semibold text-gray-700">Item</label>
                <button type="button" wire:click="loadProducts" class="text-sm font-medium text-royal hover:text-royal-600">Load Produk dari Gudang</button>
            </div>

            @if ($items)
                <div class="card overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-surface-muted">
                            <tr>
                                <th class="table-header">Produk</th>
                                <th class="table-header text-right">System</th>
                                <th class="table-header text-right">Actual</th>
                                <th class="table-header text-right">Selisih</th>
                                <th class="table-header"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border bg-surface-card">
                            @foreach ($items as $index => $item)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-navy-900">
                                        {{ $item['product_name'] ?? 'Produk' }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm text-gray-500">{{ $item['system_qty'] }}</td>
                                    <td class="px-6 py-3">
                                        <input type="number" step="0.01" wire:model="items.{{ $index }}.actual_qty" class="input-field w-28 ml-auto text-right">
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm font-medium
                                        {{ ((float) $item['actual_qty'] - (float) $item['system_qty']) > 0 ? 'text-success' : (((float) $item['actual_qty'] - (float) $item['system_qty']) < 0 ? 'text-danger' : 'text-gray-500') }}">
                                        {{ number_format((float) $item['actual_qty'] - (float) $item['system_qty'], 2) }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <button type="button" wire:click="removeItem({{ $index }})" class="rounded-lg p-2 text-danger hover:bg-danger-soft">X</button>
                                    </td>
                                </tr>
                                @error("items.{$index}.actual_qty")<tr><td colspan="5" class="px-6 pb-2 text-xs text-danger">{{ $message }}</td></tr>@enderror
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500">Pilih gudang lalu klik "Load Produk dari Gudang" untuk memuat daftar stok.</p>
            @endif

            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="btn-primary">Submit Opname</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Nomor</th>
                    <th class="table-header">Gudang</th>
                    <th class="table-header">Tanggal</th>
                    <th class="table-header text-right">Item</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($opnames as $opname)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $opname->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $opname->warehouse?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $opname->opname_date }}</td>
                        <td class="px-6 py-4 text-right text-sm text-gray-500">{{ $opname->items->count() }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold
                                {{ $opname->status === 'posted' ? 'badge-success' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($opname->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if ($opname->status === 'submitted')
                                <button wire:click="approve('{{ $opname->id }}')" class="font-medium text-success hover:text-success/80">Approve</button>
                                <button wire:click="reject('{{ $opname->id }}')" wire:confirm="Yakin reject?" class="ml-3 font-medium text-danger hover:text-danger/80">Reject</button>
                            @elseif ($opname->status === 'approved')
                                <button wire:click="post('{{ $opname->id }}')" wire:confirm="Post selisih opname?" class="font-medium text-royal hover:text-royal-600">Post</button>
                            @elseif (in_array($opname->status, ['draft', 'rejected']))
                                <button wire:click="delete('{{ $opname->id }}')" wire:confirm="Yakin hapus?" class="font-medium text-danger hover:text-danger/80">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada opname.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
