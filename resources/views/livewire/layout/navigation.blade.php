<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function navGroups(): array
    {
        return [
            'Menu' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ],
            'Master Data' => [
                ['label' => 'Kategori', 'route' => 'master.index', 'params' => ['entity' => 'categories'], 'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h6'],
                ['label' => 'Merek', 'route' => 'master.index', 'params' => ['entity' => 'brands'], 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['label' => 'Satuan', 'route' => 'master.index', 'params' => ['entity' => 'units'], 'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 00-6.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3'],
                ['label' => 'Gudang', 'route' => 'master.index', 'params' => ['entity' => 'warehouses'], 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['label' => 'Pajak', 'route' => 'master.index', 'params' => ['entity' => 'taxes'], 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3-2 2 2 2-2 2 2 2-2 3 2z'],
            ],
            'Produk & Partner' => [
                ['label' => 'Produk', 'route' => 'products.index', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                ['label' => 'Customer', 'route' => 'master.index', 'params' => ['entity' => 'customers'], 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
                ['label' => 'Supplier', 'route' => 'master.index', 'params' => ['entity' => 'suppliers'], 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ],
            'Inventory' => [
                ['label' => 'Penyesuaian', 'route' => 'inventory.adjustments.index', 'icon' => 'M12 6v6m0 0l3-3m-3 3l-3-3m3 3v8'],
                ['label' => 'Stock Opname', 'route' => 'inventory.opnames.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['label' => 'Transfer Stok', 'route' => 'inventory.transfers.index', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4'],
            ],
            'Penjualan' => [
                ['label' => 'Quotation', 'route' => 'sales.quotations.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['label' => 'Sales Order', 'route' => 'sales.orders.index', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                ['label' => 'Delivery Order', 'route' => 'sales.deliveries.index', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                ['label' => 'Invoice', 'route' => 'sales.invoices.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ],
            'Pembelian' => [
                ['label' => 'Purchase Order', 'route' => 'purchasing.orders.index', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                ['label' => 'Goods Receive', 'route' => 'purchasing.receipts.index', 'icon' => 'M5 13l4 4L19 7'],
                ['label' => 'Purchase Invoice', 'route' => 'purchasing.invoices.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ],
            'Lainnya' => [
                ['label' => 'Laporan', 'route' => 'dashboard', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['label' => 'Pengguna', 'route' => 'dashboard', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
            ],
        ];
    }
}; ?>

<div x-data="{ open: false }" @toggle-sidebar.window="open = true">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-60 bg-navy lg:flex lg:flex-col">
        <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-royal">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-white">{{ config('app.name') }}</div>
                <div class="text-xs text-slate-400">Enterprise System</div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            @foreach ($this->navGroups() as $group => $items)
                <div class="mb-1 px-3 pt-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $group }}</div>
                <ul class="space-y-1">
                    @foreach ($items as $item)
                        @php
                            $route = $item['route'];
                            $params = $item['params'] ?? [];
                            $active = request()->routeIs($route) && (empty($params) || request()->route('entity') === ($params['entity'] ?? null));
                        @endphp
                        <li>
                            <a href="{{ route($route, $params) }}"
                               wire:navigate
                               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $active ? 'bg-white/10 text-white border-l-2 border-royal' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-royal' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                </svg>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-royal text-sm font-semibold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</div>
                    <div class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Mobile sidebar -->
    <div class="lg:hidden">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-navy/60" @click="open = false"></div>
        <div x-show="open" x-cloak class="fixed inset-y-0 left-0 z-50 w-64 overflow-y-auto bg-navy">
            <div class="flex items-center justify-between p-4">
                <div class="text-sm font-semibold text-white">{{ config('app.name') }}</div>
                <button @click="open = false" class="text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="px-3">
                @foreach ($this->navGroups() as $group => $items)
                    <div class="mb-1 px-3 pt-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $group }}</div>
                    <ul class="space-y-1">
                        @foreach ($items as $item)
                            <li>
                                <a href="{{ route($item['route'], $item['params'] ?? []) }}" wire:navigate @click="open = false"
                                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                    </svg>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </nav>
        </div>
    </div>
</div>
