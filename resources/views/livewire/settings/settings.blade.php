<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-navy-900">Pengaturan Sistem</h1>
        <p class="mt-1 text-sm text-gray-500">Konfigurasi identitas perusahaan yang tampil pada aplikasi, print, dan dokumen PDF.</p>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6">
        <form wire:submit="save" class="mt-4 space-y-6">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Logo Perusahaan</h2>
                <div class="mt-3 flex items-center gap-5">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-surface-border bg-surface-muted">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" alt="Preview logo" class="h-full w-full object-contain">
                        @elseif ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo perusahaan" class="h-full w-full object-contain">
                        @else
                            <span class="material-symbols-outlined text-3xl text-gray-400">business</span>
                        @endif
                    </div>
                    <div class="space-y-2">
                        <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-royal file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-royal-600">
                        @error('logo')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                        @if ($logoUrl || $logo)
                            <button type="button" wire:click="removeLogo" wire:confirm="Hapus logo?" class="text-sm font-medium text-danger hover:text-danger/80">Hapus logo</button>
                        @endif
                        <p class="text-xs text-gray-500">PNG, JPG, SVG, atau WebP. Maks. 2 MB.</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-surface-border pt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Identitas Perusahaan</h2>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama Perusahaan</label>
                        <input type="text" wire:model="form.company_name" class="input-field mt-1" placeholder="{{ config('app.name') }}">
                        @error('form.company_name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Tagline</label>
                        <input type="text" wire:model="form.tagline" class="input-field mt-1" placeholder="Enterprise System">
                        @error('form.tagline')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700">Alamat</label>
                    <textarea wire:model="form.address" rows="2" class="input-field mt-1"></textarea>
                    @error('form.address')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Kota</label>
                        <input type="text" wire:model="form.city" class="input-field mt-1">
                        @error('form.city')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Kode Pos</label>
                        <input type="text" wire:model="form.postal_code" class="input-field mt-1">
                        @error('form.postal_code')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">NPWP</label>
                        <input type="text" wire:model="form.npwp" class="input-field mt-1">
                        @error('form.npwp')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-surface-border pt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Kontak</h2>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Email</label>
                        <input type="email" wire:model="form.email" class="input-field mt-1">
                        @error('form.email')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Telepon</label>
                        <input type="text" wire:model="form.phone" class="input-field mt-1">
                        @error('form.phone')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Website</label>
                        <input type="text" wire:model="form.website" class="input-field mt-1">
                        @error('form.website')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-surface-border pt-4">
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
