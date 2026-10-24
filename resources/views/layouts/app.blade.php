<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Kopi Satu Bang'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-white text-gray-900 antialiased">
    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur">
        <nav class="container mx-auto flex items-center justify-between gap-4 px-6 py-4" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="text-xl font-bold tracking-tight text-amber-900">
                Kopi Satu Bang
            </a>

            <div class="flex flex-wrap items-center justify-end gap-x-5 gap-y-2 text-sm font-medium">
                <a href="{{ route('home') }}" class="transition hover:text-amber-800">Beranda</a>
                <a href="{{ route('menu-minuman') }}" class="transition hover:text-amber-800">Menu Minuman</a>
                <a href="{{ route('menu-makanan') }}" class="transition hover:text-amber-800">Menu Makanan</a>
                <a href="{{ route('reservasi') }}" class="transition hover:text-amber-800">Reservasi</a>
                <a href="{{ route('login') }}" class="rounded-full bg-amber-900 px-4 py-2 text-white transition hover:bg-amber-800">Login</a>
            </div>
        </nav>
    </header>

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="mt-auto bg-gray-950 px-6 py-6 text-center text-sm text-gray-300">
        <p>&copy; {{ date('Y') }} Kopi Satu Bang. Semua hak dilindungi.</p>
    </footer>

    @stack('scripts')
    @stack('extra-scripts')
</body>
</html>
