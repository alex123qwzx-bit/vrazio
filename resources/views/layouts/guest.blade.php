<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Atelier Authentication') | VRAZIO</title>

    {{-- Favicon & App Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Resilient Production Vite Assets with Fallback --}}
    @php
        $viteRendered = false;
        try {
            if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('build/.vite/manifest.json'))) {
                echo app(\Illuminate\Foundation\Vite::class)(['resources/css/app.css', 'resources/js/app.js'])->toHtml();
                $viteRendered = true;
            }
        } catch (\Throwable $e) {
            $viteRendered = false;
        }
    @endphp
    @if(!$viteRendered)
        <link rel="stylesheet" href="{{ asset('build/assets/app-Bvlct9fP.css') }}">
        <script type="module" src="{{ asset('build/assets/app-B1y9mKpQ.js') }}"></script>
    @endif
</head>
<body class="bg-[#0A1C15] text-[#FAF8F5] min-h-screen flex flex-col justify-between font-body antialiased">
    <header class="py-8 text-center border-b border-[#D4AF37]/20">
        <a href="{{ route('home') }}" class="inline-block">
            <span class="font-display text-3xl md:text-4xl tracking-[0.25em] font-bold text-[#D4AF37]">V R A Z I O</span>
            <span class="block text-[9px] uppercase tracking-[0.4em] text-[#D4AF37]/70 mt-1">Haute Joaillerie &bull; Master Atelier</span>
        </a>
    </header>

    <main class="flex-grow flex items-center justify-center p-6">
        <div class="w-full max-w-md bg-[#0E261C] border border-[#D4AF37]/30 p-8 shadow-2xl">
            @yield('content')
        </div>
    </main>

    <footer class="py-6 text-center text-xs text-[#FAF8F5]/40 border-t border-[#D4AF37]/10">
        &copy; {{ date('Y') }} VRAZIO Atelier. All Rights Reserved. Private Vault Security.
    </footer>
</body>
</html>
