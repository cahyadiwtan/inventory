<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Laporan</h1>
            <p class="mt-1 text-sm text-gray-500">Pilih laporan, atur filter, lalu ekspor ke Excel atau PDF.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <aside class="lg:col-span-3">
            <div class="card p-4 space-y-6">
                @foreach ($groups as $groupName => $reports)
                    <div>
                        <h3 class="px-2 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $groupName }}</h3>
                        <div class="mt-2 space-y-1">
                            @foreach ($reports as $r)
                                <button type="button"
                                    wire:click="selectReport('{{ $r['key'] }}', '{{ $groupName }}')"
                                    class="block w-full rounded-md px-3 py-2 text-left text-sm transition-colors {{ $this->report === $r['key'] ? 'bg-royal font-semibold text-white' : 'text-navy-700 hover:bg-surface-muted' }}">
                                    {{ $r['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>

        <section class="lg:col-span-9">
            <div class="card overflow-hidden">
                <div class="flex flex-wrap items-center gap-4 border-b border-surface-border px-6 py-4">
                    <h2 class="text-lg font-semibold text-navy-900">{{ $result['title'] }}</h2>
                    <div class="ml-auto flex items-center gap-2">
                        @if ($reportService->definitions()[$this->report]['range'] ?? false)
                            <input type="date" wire:model.live="from" class="input-field">
                            <span class="text-gray-400">s/d</span>
                            <input type="date" wire:model.live="to" class="input-field">
                        @endif
                        @if ($reportService->definitions()[$this->report]['period'] ?? false)
                            <select wire:model.live="period" class="input-field">
                                <option value="harian">Harian</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="tahunan">Tahunan</option>
                            </select>
                        @endif
                        @if ($reportService->definitions()[$this->report]['warehouse'] ?? false)
                            <select wire:model.live="warehouse" class="input-field">
                                <option value="">Semua Gudang</option>
                                @foreach ($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <button wire:click="exportExcel" class="btn-secondary">Excel</button>
                        <button wire:click="exportPdf" class="btn-primary">PDF</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-surface-muted">
                            <tr>
                                @foreach ($result['headings'] as $heading)
                                    <th class="table-header">{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border bg-surface-card">
                            @forelse ($result['rows'] as $row)
                                <tr class="hover:bg-surface-muted">
                                    @foreach ($row as $cell)
                                        <td class="px-6 py-3 text-sm text-navy-900">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($result['headings']) }}" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Tidak ada data untuk laporan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
