<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <livewire:layout.navigation />

        <div class="min-h-screen bg-surface pt-16 lg:pl-60">
            <!-- Topbar -->
            <header class="fixed inset-x-0 top-0 z-30 h-16 border-b border-line bg-white/90 backdrop-blur lg:pl-60">
                <div class="flex h-full items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button x-data @click="$dispatch('toggle-sidebar')" class="rounded-lg p-2 text-ink hover:bg-surface lg:hidden">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="text-lg font-semibold text-ink">{{ $title ?? config('app.name') }}</h1>
                            @endisset
                        </div>
                    </div>
                    <livewire:notification-bell />
                </div>
            </header>

            <!-- Page content -->
            <main class="px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
