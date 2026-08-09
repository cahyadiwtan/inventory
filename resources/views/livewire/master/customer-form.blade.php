<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <a href="{{ route('master.index', ['entity' => 'customers']) }}" wire:navigate class="mb-2 inline-flex items-center gap-1 text-sm font-medium text-gray-500 transition-colors hover:text-royal">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Customers
        </a>
        <h1 class="text-2xl font-semibold text-navy-900">{{ $editing ? 'Edit Customer' : 'Tambah Customer' }}</h1>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-6">
        <form wire:submit="save" class="mt-4 space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Code <span class="text-danger">*</span></label>
                    <input type="text" wire:model="form.code" class="input-field mt-1">
                    @error('form.code')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="form.name" class="input-field mt-1">
                    @error('form.name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">NPWP</label>
                <input type="text" wire:model="form.npwp" class="input-field mt-1">
                @error('form.npwp')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Address <span class="text-danger">*</span></label>
                <textarea wire:model="form.address" rows="3" class="input-field mt-1"></textarea>
                @error('form.address')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Phone</label>
                    <input type="text" wire:model="form.phone" class="input-field mt-1">
                    @error('form.phone')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" wire:model="form.email" class="input-field mt-1">
                    @error('form.email')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">PIC</label>
                    <input type="text" wire:model="form.pic_name" class="input-field mt-1">
                    @error('form.pic_name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Payment Term (Days) <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" wire:model="form.payment_term_days" class="input-field mt-1">
                    @error('form.payment_term_days')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Credit Limit <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" wire:model="form.credit_limit" class="input-field mt-1">
                @error('form.credit_limit')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" wire:click="cancel" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
