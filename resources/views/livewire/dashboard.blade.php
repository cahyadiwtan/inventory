<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-navy-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Ringkasan operasional {{ date('d M Y') }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="card p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Produk</div>
            <div class="mt-2 text-2xl font-bold text-navy-900">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="card p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Nilai Stok (cost)</div>
            <div class="mt-2 text-2xl font-bold text-navy-900">{{ number_format($stockValue, 0) }}</div>
        </div>
        <div class="card p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">SO Aktif</div>
            <div class="mt-2 text-2xl font-bold text-navy-900">{{ number_format($openOrders) }}</div>
        </div>
        <div class="card p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Piutang</div>
            <div class="mt-2 text-2xl font-bold text-warning">{{ number_format($receivable, 0) }}</div>
        </div>
        <div class="card p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Hutang</div>
            <div class="mt-2 text-2xl font-bold text-danger">{{ number_format($payable, 0) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-navy-900">Penjualan 6 Bulan Terakhir</h2>
            @php $max = max(1, max(array_column($salesTrend, 'total'))); @endphp
            <div class="mt-6 flex h-56 items-end gap-3">
                @foreach ($salesTrend as $point)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="text-xs font-medium text-navy-600">{{ number_format($point['total'], 0) }}</div>
                        <div class="w-full rounded-t-lg bg-royal/80 transition-colors hover:bg-royal" style="height: {{ max(2, ($point['total'] / $max) * 160) }}px"></div>
                        <div class="text-xs font-semibold text-gray-500">{{ $point['month'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-lg font-semibold text-navy-900">Stok per Gudang</h2>
            <div class="mt-4 space-y-3">
                @forelse ($warehouseStocks as $warehouse)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-navy-800">{{ $warehouse->name }}</span>
                            <span class="text-gray-500">{{ number_format((float) $warehouse->stock_rows_sum_qty_on_hand, 0) }}</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-surface-muted">
                            <div class="h-2 rounded-full bg-navy" style="width: {{ $warehouseStocks->max(fn ($w) => (float) $w->stock_rows_sum_qty_on_hand) > 0 ? (float) $warehouse->stock_rows_sum_qty_on_hand / $warehouseStocks->max(fn ($w) => (float) $w->stock_rows_sum_qty_on_hand) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Belum ada data gudang.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-surface-border px-6 py-4">
                <h2 class="text-lg font-semibold text-navy-900">Stok Menipis</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-surface-muted">
                    <tr>
                        <th class="table-header">Produk</th>
                        <th class="table-header text-right">Stok</th>
                        <th class="table-header text-right">Reorder</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border bg-surface-card">
                    @forelse ($lowStocks as $low)
                        <tr class="hover:bg-surface-muted">
                            <td class="px-6 py-3 text-sm text-navy-900">{{ $low->code }} - {{ $low->name }}</td>
                            <td class="px-6 py-3 text-right text-sm font-medium text-danger">{{ number_format((float) $low->total_stock, 0) }}</td>
                            <td class="px-6 py-3 text-right text-sm text-gray-500">{{ number_format((float) $low->reorder_point, 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">Semua stok aman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-surface-border px-6 py-4">
                <h2 class="text-lg font-semibold text-navy-900">Aktivitas Terbaru</h2>
            </div>
            <div class="divide-y divide-surface-border">
                @forelse ($recentActivities as $activity)
                    <div class="flex items-start gap-3 px-6 py-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-royal"></span>
                        <div class="min-w-0">
                            <div class="truncate text-sm text-navy-800">
                                {{ $activity->description ?: $activity->event }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $activity->log_name }} · {{ $activity->created_at?->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">Belum ada aktivitas.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
