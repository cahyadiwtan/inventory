<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full max-w-md rounded-xl border border-surface-outline bg-white p-8 shadow-xl">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 rotate-3 transform items-center justify-center rounded-2xl bg-navy-100 shadow-md">
            <span class="material-symbols-outlined -rotate-3 text-[32px] text-navy-600">security</span>
        </div>
        <h1 class="mb-2 text-3xl font-bold tracking-tight text-navy-900">Welcome Back</h1>
        <p class="text-sm text-navy-500">Secure access to {{ config('app.name') }} systems.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-success/20 bg-success-soft p-3 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Work Email -->
        <div>
            <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-navy-800">
                Work Email
            </label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-navy-400">mail</span>
                <input wire:model="form.email" id="email" type="email" name="email" placeholder="name@company.com"
                    required autofocus autocomplete="username"
                    class="h-12 w-full rounded-lg border border-surface-outline bg-white pl-10 pr-3 text-sm text-navy-900 outline-none transition-all placeholder:text-gray-400 focus:border-navy focus:ring-2 focus:ring-navy/20" />
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="block text-xs font-semibold uppercase tracking-widest text-navy-800">
                    Password
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-xs text-royal transition-colors hover:text-navy">
                        Forgot?
                    </a>
                @endif
            </div>
            <div class="relative" x-data="{ show: false }">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-navy-400">lock</span>
                <input wire:model="form.password" id="password" type="password" name="password" placeholder="••••••••"
                    :type="show ? 'text' : 'password'" required autocomplete="current-password"
                    class="h-12 w-full rounded-lg border border-surface-outline bg-white pl-10 pr-10 text-sm text-navy-900 outline-none transition-all placeholder:text-gray-400 focus:border-navy focus:ring-2 focus:ring-navy/20" />
                <button type="button" @click="show = !show"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-navy-400 transition-colors hover:text-navy-900">
                    <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility' : 'visibility_off'"></span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label class="flex cursor-pointer items-center gap-2">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember"
                    class="h-4 w-4 rounded border-surface-outline bg-white text-navy focus:ring-2 focus:ring-navy/30" />
                <span class="text-sm text-navy-500">Remember me</span>
            </label>
        </div>

        <!-- Submit -->
        <button type="submit"
            class="group flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-navy text-xs font-semibold uppercase tracking-wider text-white shadow-md transition-all hover:bg-navy-800 hover:shadow-lg">
            <span>Sign In</span>
            <span class="material-symbols-outlined text-[20px] transition-transform group-hover:translate-x-1">arrow_forward</span>
        </button>
    </form>

    <!-- SSO Divider -->
    <div class="relative mt-6 flex items-center justify-center">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-surface-border"></div>
        </div>
        <span class="relative bg-white px-3 text-[11px] font-semibold uppercase tracking-widest text-gray-400">Or continue with</span>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4">
        <button type="button"
            class="flex h-12 items-center justify-center gap-2 rounded-lg border border-surface-outline bg-white text-xs font-semibold uppercase tracking-wide text-navy-800 transition-colors hover:bg-surface-muted">
            <span class="material-symbols-outlined text-[20px]">cloud</span>
            Azure AD
        </button>
        <button type="button"
            class="flex h-12 items-center justify-center gap-2 rounded-lg border border-surface-outline bg-white text-xs font-semibold uppercase tracking-wide text-navy-800 transition-colors hover:bg-surface-muted">
            <span class="material-symbols-outlined text-[20px]">domain</span>
            Okta
        </button>
    </div>

    <!-- Role Context -->
    <div class="mt-8 flex items-start gap-3 rounded-lg border border-surface-border bg-surface-muted p-4">
        <span class="material-symbols-outlined mt-0.5 text-royal">info</span>
        <div>
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-navy-800">Role Context</span>
            <span class="text-sm text-navy-500">
                Are you signing in as Warehouse Staff? Access the
                <a href="#" class="text-royal underline-offset-2 hover:underline">Terminal View</a>
                for optimized barcode scanning.
            </span>
        </div>
    </div>
</div>
