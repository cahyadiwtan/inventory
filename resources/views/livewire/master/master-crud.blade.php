<div class="space-y-6">
    @php
        $separateForm = in_array($entity, ['customers', 'suppliers']);
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">{{ $config['title'] }}</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola data {{ strtolower($config['title']) }}</p>
        </div>
        <div class="flex items-center gap-3">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari kode / nama..."
                class="input-field !w-64"
            >
            @if ($separateForm)
                <a href="{{ route('master.'.$entity.'.create') }}" wire:navigate class="btn-primary">
                    + Tambah
                </a>
            @else
                <button wire:click="openCreate" class="btn-primary">
                    + Tambah
                </button>
            @endif
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
                    @foreach ($config['fields'] as $meta)
                        <th class="table-header">{{ $meta['label'] }}</th>
                    @endforeach
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($items as $item)
                    <tr class="hover:bg-surface-muted">
                        @foreach ($config['fields'] as $field => $meta)
                            <td class="px-6 py-4 text-sm text-navy-900">{{ $item->{$field} }}</td>
                        @endforeach
                        <td class="px-6 py-4 text-right">
                            <button
                                wire:click="toggleActive('{{ $item->id }}')"
                                class="rounded-md px-3 py-1 text-xs font-semibold {{ $item->is_active ? 'badge-success' : 'bg-gray-100 text-gray-500' }}"
                            >
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if ($separateForm)
                                <a href="{{ route('master.'.$entity.'.edit', $item) }}" wire:navigate class="font-medium text-royal hover:text-royal-600">Edit</a>
                            @else
                                <button wire:click="openEdit('{{ $item->id }}')" class="font-medium text-royal hover:text-royal-600">Edit</button>
                            @endif
                            <button wire:click="delete('{{ $item->id }}')" wire:confirm="Yakin hapus?" class="ml-3 font-medium text-danger hover:text-danger/80">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($config['fields']) + 2 }}" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $items->links() }}</div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-navy-900/50" wire:click.self="resetForm">
            <div class="card w-full max-w-lg p-6 shadow-dropdown">
                <h2 class="text-lg font-semibold text-navy-900">
                    {{ $editingId ? 'Edit' : 'Tambah' }} {{ $config['title'] }}
                </h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    @foreach ($config['fields'] as $field => $meta)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">
                                {{ $meta['label'] }} @if ($meta['required']) <span class="text-danger">*</span> @endif
                            </label>
                            @if ($meta['type'] === 'textarea')
                                <textarea
                                    wire:model="form.{{ $field }}"
                                    rows="3"
                                    class="input-field mt-1"
                                ></textarea>
                            @else
                                <input
                                    type="{{ $meta['type'] }}"
                                    step="{{ $meta['type'] === 'number' ? '0.01' : '' }}"
                                    wire:model="form.{{ $field }}"
                                    class="input-field mt-1"
                                >
                            @endif
                            @error("form.{$field}")
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="resetForm" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
