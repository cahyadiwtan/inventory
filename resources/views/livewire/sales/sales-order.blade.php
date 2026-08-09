<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Sales Order</h1>
            <p class="mt-1 text-sm text-gray-500">Konversi quotation → sales order (draft → approved)</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-navy-900">Konversi dari Quotation</h2>

        <form wire:submit="convert" class="mt-4 space-y-4">
            <div class="grid grid-cols-[1fr_auto] items-end gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Quotation Diterima <span class="text-danger">*</span></label>
                    <select wire:model="quotationId" class="input-field mt-1">
                        <option value="">-- Pilih Quotation --</option>
                        @foreach ($convertibleQuotations as $quotation)
                            <option value="{{ $quotation->id }}">{{ $quotation->number }} - {{ $quotation->customer?->name }} ({{ number_format((float) $quotation->total, 2) }})</option>
                        @endforeach
                    </select>
                    @error('quotationId')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn-primary">Convert → SO</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-surface-muted">
                <tr>
                    <th class="table-header">Nomor</th>
                    <th class="table-header">Quotation</th>
                    <th class="table-header">Customer</th>
                    <th class="table-header">Tanggal</th>
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
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->quotation?->number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->customer?->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->order_date?->format('d M Y') }}</td>
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
                                    : ($order->status === 'cancelled' ? 'badge-danger' : 'bg-gray-100 text-gray-500') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if ($order->status === 'draft')
                                <button wire:click="approve('{{ $order->id }}')" class="font-medium text-success hover:text-success/80">Approve</button>
                                <button wire:click="cancel('{{ $order->id }}')" wire:confirm="Yakin batalkan?" class="ml-3 font-medium text-danger hover:text-danger/80">Cancel</button>
                            @endif
                            <a href="{{ route('sales.orders.show', $order) }}" wire:navigate class="{{ $order->status === 'draft' ? 'ml-3 ' : '' }}font-medium text-royal hover:text-royal/80">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada sales order.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
