<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">Import / Export Data</h1>
            <p class="mt-1 text-sm text-gray-500">Transfer data master (Excel) masuk & keluar sistem</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($configs as $key => $item)
            <button
                wire:click="$set('entity', '{{ $key }}')"
                class="card p-4 text-left transition-colors {{ $entity === $key ? 'border-royal ring-2 ring-royal/20' : 'hover:border-royal/50' }}"
            >
                <div class="flex items-center justify-between">
                    <div class="text-sm font-semibold text-navy-900">{{ $item['title'] }}</div>
                    <span class="rounded-full bg-surface-muted px-2 py-0.5 text-xs text-gray-500">{{ $item['count'] }}</span>
                </div>
                <div class="mt-1 text-xs text-gray-500">{{ $key }}</div>
            </button>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-navy-900">Export {{ $config['title'] }}</h2>
            <p class="mt-1 text-sm text-gray-500">Unduh seluruh data {{ strtolower($config['title']) }} sebagai file Excel.</p>

            <div class="mt-4">
                <button wire:click="export" class="btn-primary">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Export ke Excel
                </button>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-lg font-semibold text-navy-900">Import {{ $config['title'] }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                Unggah file Excel. Baris dengan kode yang sudah ada akan diperbarui, sisanya dibuat baru.
            </p>

            <div class="mt-4 space-y-4">
                <div>
                    <button wire:click="downloadTemplate" class="text-sm font-medium text-royal hover:text-royal-600">
                        Unduh template (.xlsx)
                    </button>
                </div>

                <div class="flex items-start gap-3">
                    <input
                        type="file"
                        wire:model="file"
                        accept=".xlsx,.xls,.csv"
                        class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-surface-muted file:px-4 file:py-2 file:text-sm file:font-semibold file:text-navy-900 hover:file:bg-surface-outline"
                    >
                    <button wire:click="import" wire:loading.attr="disabled" class="btn-secondary shrink-0">
                        <span wire:loading.remove wire:target="import">Import</span>
                        <span wire:loading wire:target="import">Memproses...</span>
                    </button>
                </div>
                @error('file')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    @if (count($summary) > 0)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-navy-900">Hasil Import</h2>
            <div class="mt-3 grid grid-cols-3 gap-4">
                <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-center">
                    <div class="text-2xl font-bold text-success">{{ $summary['created'] }}</div>
                    <div class="text-xs text-gray-500">Dibuat</div>
                </div>
                <div class="rounded-lg border border-royal/20 bg-royal-soft px-4 py-3 text-center">
                    <div class="text-2xl font-bold text-royal">{{ $summary['updated'] }}</div>
                    <div class="text-xs text-gray-500">Diperbarui</div>
                </div>
                <div class="rounded-lg border border-warning/20 bg-warning-soft px-4 py-3 text-center">
                    <div class="text-2xl font-bold text-warning">{{ $summary['skipped'] }}</div>
                    <div class="text-xs text-gray-500">Dilewati</div>
                </div>
            </div>
        </div>
    @endif

    @if (count($importErrors) > 0)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-navy-900">Baris Bermasalah</h2>
            <ul class="mt-3 max-h-64 space-y-1 overflow-y-auto">
                @foreach ($importErrors as $error)
                    <li class="rounded-lg bg-danger-soft px-3 py-2 text-sm text-danger">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>