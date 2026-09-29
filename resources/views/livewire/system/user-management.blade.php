<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-navy-900">User Management</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola pengguna & peran (role) akses sistem</p>
        </div>
        <div class="flex items-center gap-3">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama / email..."
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
                    <th class="table-header">Nama</th>
                    <th class="table-header">Email</th>
                    <th class="table-header">Role</th>
                    <th class="table-header text-right">Status</th>
                    <th class="table-header text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border bg-surface-card">
                @forelse ($items as $item)
                    <tr class="hover:bg-surface-muted">
                        <td class="px-6 py-4 text-sm font-medium text-navy-900">
                            {{ $item->name }}
                            @if ($item->id === auth()->id())
                                <span class="ml-1 text-xs text-gray-400">(Anda)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($item->roles as $role)
                                    <span class="badge-success">{{ $role->name }}</span>
                                @empty
                                    <span class="text-xs text-gray-400">Tanpa role</span>
                                @endforelse
                            </div>
                        </td>
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
                            @if ($item->id !== auth()->id())
                                <button wire:click="delete('{{ $item->id }}')" wire:confirm="Yakin hapus?" class="ml-3 font-medium text-danger hover:text-danger/80">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
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
                    {{ $editingId ? 'Edit' : 'Tambah' }} User
                </h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama <span class="text-danger">*</span></label>
                        <input type="text" wire:model="form.name" class="input-field mt-1">
                        @error('form.name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Email <span class="text-danger">*</span></label>
                        <input type="email" wire:model="form.email" class="input-field mt-1">
                        @error('form.email')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">
                            Password @if (!$editingId) <span class="text-danger">*</span> @endif
                        </label>
                        <input type="password" wire:model="form.password" placeholder="{{ $editingId ? 'Kosongkan jika tidak diubah' : '' }}" class="input-field mt-1">
                        @error('form.password')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Role</label>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach ($availableRoles as $role)
                                <label class="flex items-center gap-2 rounded-lg border border-surface-border px-3 py-2 text-sm text-gray-700 hover:bg-surface-muted">
                                    <input
                                        type="checkbox"
                                        value="{{ $role->name }}"
                                        wire:model="roles"
                                        class="rounded border-surface-outline text-royal focus:ring-royal"
                                    >
                                    {{ $role->name }}
                                </label>
                            @endforeach
                        </div>
                        @error('roles')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
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