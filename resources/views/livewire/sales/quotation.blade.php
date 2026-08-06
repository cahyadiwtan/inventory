<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Quotation</h1>
            <p class="mt-1 text-sm text-gray-500">Buat penawaran harga (draft → sent → accepted/rejected/expired)</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-navy-900">Buat Quotation</h2>

        <form wire:submit="save" class="mt-4 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Customer <span class="text-danger">*</span></label>
                    <select wire:model="customerId" class="input-field mt-1">
                        <option value="">-- Pilih --</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customerId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div></div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" wire:model="quotationDate" class="input-field mt-1">
                    @error('quotationDate')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Berlaku Sampai <span class="text-danger">*</span></label>
                    <input type="date" wire:model="validUntil" class="input-field mt-1">
                    @error('validUntil')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
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
                <button type="submit" class="btn-primary">Simpan Quotation</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Nomor</th>
                    <th class="table-header">Customer</th>
                    <th class="table-header">Tanggal</th>
                    <th class="table-header">Berlaku</th>
                    <th class="table-header">Item</th>
                    <th class="table-header text-right">Total</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($quotations as $quotation)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">{{ $quotation->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $quotation->customer?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $quotation->quotation_date?->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $quotation->valid_until?->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @foreach ($quotation->items as $item)
                                {{ $item->product?->name }} ({{ $item->qty }})
                                @if (! $loop->last), @endif
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-navy-900">
                            {{ number_format((float) $quotation->total, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="rounded-md px-3 py-1 text-xs font-semibold
                                {{ $quotation->status === 'accepted' ? 'badge-success'
                                    : ($quotation->status === 'rejected' ? 'badge-danger'
                                    : ($quotation->status === 'expired' ? 'badge-warning' : 'bg-gray-100 text-gray-500')) }}">
                                {{ ucfirst($quotation->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if ($quotation->status === 'draft')
                                <button wire:click="send('{{ $quotation->id }}')" class="font-medium text-royal hover:text-royal-600">Send</button>
                                <button wire:click="expire('{{ $quotation->id }}')" class="ml-3 font-medium text-gray-500 hover:text-gray-700">Expire</button>
                            @elseif ($quotation->status === 'sent')
                                <button wire:click="accept('{{ $quotation->id }}')" class="font-medium text-success hover:text-success/80">Accept</button>
                                <button wire:click="reject('{{ $quotation->id }}')" wire:confirm="Yakin reject?" class="ml-3 font-medium text-danger hover:text-danger/80">Reject</button>
                            @elseif ($quotation->isConvertible())
                                <button wire:click="convert('{{ $quotation->id }}')" wire:confirm="Konversi ke Sales Order?" class="font-medium text-royal hover:text-royal-600">Convert → SO</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada quotation.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
