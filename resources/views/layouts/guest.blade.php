<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? \App\Support\CompanyProfile::name() }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-navy-900 antialiased bg-surface">
        <div class="relative flex min-h-screen flex-col overflow-hidden">
            <div class="pointer-events-none absolute -left-[10%] -top-[20%] h-1/2 w-1/2 rounded-full bg-navy/5 blur-[120px]"></div>
            <div class="pointer-events-none absolute -bottom-[20%] -right-[10%] h-1/2 w-1/2 rounded-full bg-royal/5 blur-[120px]"></div>

            <!-- Header -->
            <header class="fixed top-0 z-50 flex h-16 w-full items-center px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-navy">
                        <span class="material-symbols-outlined text-[20px] text-white">hub</span>
                    </div>
                    <span class="text-xl font-semibold tracking-tight text-navy">{{ \App\Support\CompanyProfile::name() }}</span>
                </div>
            </header>

            <!-- Content -->
            <main class="relative z-10 flex flex-1 items-center justify-center px-6 py-10">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="fixed bottom-0 z-50 flex h-12 w-full items-center justify-center gap-6 px-6 text-xs font-semibold text-navy-400 opacity-60 transition-opacity hover:opacity-100">
                <a href="#" class="hover:text-navy">Privacy Policy</a>
                <a href="#" class="hover:text-navy">Terms of Service</a>
                <a href="#" class="hover:text-navy">Support</a>
                <span>&copy; {{ date('Y') }} {{ \App\Support\CompanyProfile::name() }}</span>
            </footer>
        </div>
    </body>
</html>
