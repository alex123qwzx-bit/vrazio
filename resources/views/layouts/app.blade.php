<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Haute Joaillerie & Fine Jewellery') | VRAZIO Atelier</title>
    <meta name="description" content="@yield('meta_description', 'VRAZIO — Haute Joaillerie. Discover exquisite handcrafted fine jewellery, BIS hallmarked and GIA certified. Rings, necklaces, bracelets, earrings & solitaires crafted by master artisans.')">
    <meta name="keywords" content="@yield('meta_keywords', 'VRAZIO, fine jewellery, diamond ring, haute joaillerie, BIS hallmarked gold, GIA certified diamond, luxury jewellery India, bespoke couture')">
    <meta name="author" content="VRAZIO Luxury Atelier">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    {{-- Open Graph / Social Sharing Preview --}}
    <meta property="og:site_name" content="VRAZIO">
    <meta property="og:title" content="@yield('title', 'Haute Joaillerie & Fine Jewellery') | VRAZIO">
    <meta property="og:description" content="@yield('meta_description', 'VRAZIO — Haute Joaillerie. Discover exquisite handcrafted fine jewellery, BIS hallmarked and GIA certified.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.png'))">
    <meta property="og:image:secure_url" content="@yield('og_image', asset('images/og-default.png'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="VRAZIO Haute Joaillerie Luxury Fine Jewellery">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:locale" content="en_IN">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Haute Joaillerie & Fine Jewellery') | VRAZIO">
    <meta name="twitter:description" content="@yield('meta_description', 'VRAZIO — Haute Joaillerie. Discover exquisite handcrafted fine jewellery, BIS hallmarked and GIA certified.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.png'))">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon & App Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Google Analytics GA4 — Consent-Gated (LEG-02) --}}
    @if(config('services.firebase.measurement_id'))
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            function initGA4() {
                if (window._ga4Initialized) return;
                window._ga4Initialized = true;
                var s = document.createElement('script');
                s.async = true;
                s.src = "https://www.googletagmanager.com/gtag/js?id={{ config('services.firebase.measurement_id') }}";
                document.head.appendChild(s);
                gtag('js', new Date());
                gtag('config', '{{ config('services.firebase.measurement_id') }}', { 'send_page_view': true });
            }
            try {
                if (localStorage.getItem('vrazio_cookie_consent') === 'accepted') {
                    initGA4();
                }
            } catch(e) {}
        </script>
    @endif

    {{-- JSON-LD Structured Data Schema --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "{{ url('/') }}/#organization",
                "name": "VRAZIO",
                "url": "{{ url('/') }}",
                "logo": {
                    "@type": "ImageObject",
                    "url": "{{ asset('images/android-chrome-512x512.png') }}",
                    "width": 512,
                    "height": 512
                },
                "contactPoint": {
                    "@type": "ContactPoint",
                    "telephone": "+91-89796-22310",
                    "contactType": "Customer Support",
                    "areaServed": "IN",
                    "availableLanguage": ["English", "Hindi"]
                },
                "sameAs": [
                    "https://www.instagram.com/vrazio"
                ]
            },
            {
                "@type": "WebSite",
                "@id": "{{ url('/') }}/#website",
                "url": "{{ url('/') }}",
                "name": "VRAZIO",
                "description": "Haute Joaillerie & Bespoke Couture Atelier",
                "publisher": {
                    "@id": "{{ url('/') }}/#organization"
                },
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "{{ url('/search') }}?q={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
        ]
    }
    </script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
        @if(file_exists(public_path('build/assets/app-Bvlct9fP.css')))
            <link rel="stylesheet" href="{{ asset('build/assets/app-Bvlct9fP.css') }}">
            <script type="module" src="{{ asset('build/assets/app-B1y9mKpQ.js') }}"></script>
        @else
            <link rel="stylesheet" href="{{ asset('css/app.css') }}">
            <script type="module" src="{{ asset('js/app.js') }}"></script>
        @endif
    @endif

    {{-- Livewire Styles --}}
    @livewireStyles

    @stack('head')

    <style>
        [x-cloak] { display: none !important; }

        @keyframes ticker {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .animate-ticker {
            animation: ticker 20s linear infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        .text-shimmer {
            background: linear-gradient(90deg, #D4AF37 0%, #F3E5AB 25%, #D4AF37 50%, #F3E5AB 75%, #D4AF37 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 4s linear infinite;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .animate-bounce-subtle {
            animation: bounce-subtle 2s ease-in-out infinite;
        }

        @keyframes checkmark-draw {
            to { stroke-dashoffset: 0; }
        }
        .checkmark-animate {
            stroke-dasharray: 50;
            stroke-dashoffset: 50;
            animation: checkmark-draw 0.5s ease-out 0.3s forwards;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #FAF8F5; }
        ::-webkit-scrollbar-thumb { background: #D4AF37; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #C5A028; }
    </style>
</head>
<body class="font-body bg-vrazio-ivory text-vrazio-forest antialiased"
      x-data="{
          mobileMenuOpen: false,
          cartDrawerOpen: false,
          searchOpen: false,
          announcementVisible: true
      }">

    {{-- ═══════════════════════════════════════════════════════════════════
         ANNOUNCEMENT BAR
    ═══════════════════════════════════════════════════════════════════ --}}
    @if(isset($themeSettings['announcement_bar']['enabled']) && $themeSettings['announcement_bar']['enabled'])
    <div x-show="announcementVisible"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 max-h-12"
         x-transition:leave-end="opacity-0 max-h-0"
         class="relative bg-vrazio-forest text-vrazio-gold overflow-hidden z-50">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between">
            <div class="flex-1 overflow-hidden">
                <p class="text-sm font-medium tracking-wide animate-ticker whitespace-nowrap">
                    {{ $themeSettings['announcement_bar']['text'] ?? '✦ Complimentary Armoured Transit on All Orders ✦ BIS Hallmarked & GIA Certified ✦ 15-Day Hassle-Free Returns ✦' }}
                </p>
            </div>
            <button @click="announcementVisible = false"
                    class="ml-4 flex-shrink-0 text-vrazio-gold/70 hover:text-vrazio-gold transition-colors"
                    aria-label="Close announcement">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         NAVIGATION HEADER
    ═══════════════════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-40 bg-vrazio-ivory/95 backdrop-blur-md border-b border-vrazio-gold/10 shadow-sm">
        {{-- Top Bar --}}
        <div class="hidden lg:block bg-vrazio-forest text-white/85">
            <div class="max-w-7xl mx-auto px-6 py-2 flex items-center justify-between text-xs tracking-wide">
                <div class="flex items-center gap-6">
                    <a href="https://wa.me/918979622310?text=Hi%20VRAZIO%2C%20I%20have%20a%20query" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1.5 hover:text-vrazio-gold transition-colors text-emerald-400 font-medium">
                        <svg width="14" height="14" style="width: 14px; height: 14px; display: inline-block;" class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.761.821 2.796.821h.005c3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.773-5.768zm7.135 5.769c-.002 3.935-3.201 7.134-7.136 7.134-1.257 0-2.434-.332-3.468-.944l-4.562 1.196 1.217-4.444c-.672-1.076-1.027-2.327-1.026-3.61.002-3.935 3.202-7.135 7.137-7.135 3.935 0 7.138 3.2 7.138 7.133z"/></svg>
                        <span>WhatsApp: +91 89796 22310</span>
                    </a>
                    <a href="mailto:vrazioofficial@gmail.com" class="flex items-center gap-1.5 hover:text-vrazio-gold transition-colors">
                        <svg width="14" height="14" style="width: 14px; height: 14px; display: inline-block;" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>vrazioofficial@gmail.com</span>
                    </a>
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ route('orders.index') }}" class="text-vrazio-gold font-semibold hover:underline transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>My Orders &amp; Warranty</span>
                    </a>
                    <a href="{{ route('orders.track') ?? '#' }}" class="hover:text-vrazio-gold transition-colors">Track Order</a>
                    <a href="{{ route('shipping-policy') ?? '#' }}" class="hover:text-vrazio-gold transition-colors">Shipping</a>
                    <a href="{{ route('faq') ?? '#' }}" class="hover:text-vrazio-gold transition-colors">Help &amp; FAQ</a>
                </div>
            </div>
        </div>

        {{-- Main Navigation --}}
        <nav class="max-w-7xl mx-auto px-4 lg:px-6">
            <div class="flex items-center justify-between h-16 lg:h-20">

                {{-- Mobile Menu Button --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 -ml-2 text-vrazio-forest hover:text-vrazio-gold transition-colors"
                        aria-label="Toggle mobile menu">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center gap-1 group">
                    <span class="font-heading text-2xl lg:text-3xl font-bold tracking-[0.2em] text-vrazio-forest group-hover:text-vrazio-forest/90 transition-colors">
                        VR<span class="text-vrazio-gold">A</span>ZIO
                    </span>
                </a>

                {{-- Desktop Navigation Links --}}
                <div class="hidden lg:flex items-center gap-10">
                    {{-- Collections Dropdown --}}
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative">
                        <a href="{{ route('products.index') ?? '#' }}"
                           class="flex items-center gap-1 text-sm font-medium tracking-widest uppercase text-vrazio-forest hover:text-vrazio-gold transition-colors py-2">
                            Collections
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             x-cloak
                             class="absolute top-full left-1/2 -translate-x-1/2 mt-1 w-56 bg-white rounded-lg shadow-xl border border-vrazio-gold/10 py-3 z-50">
                            <a href="{{ url('/collections?category=rings') }}" class="block px-5 py-2.5 text-sm text-vrazio-forest hover:bg-vrazio-gold/5 hover:text-vrazio-gold transition-colors tracking-wide">Rings</a>
                            <a href="{{ url('/collections?category=necklaces') }}" class="block px-5 py-2.5 text-sm text-vrazio-forest hover:bg-vrazio-gold/5 hover:text-vrazio-gold transition-colors tracking-wide">Necklaces</a>
                            <a href="{{ url('/collections?category=bracelets') }}" class="block px-5 py-2.5 text-sm text-vrazio-forest hover:bg-vrazio-gold/5 hover:text-vrazio-gold transition-colors tracking-wide">Bracelets</a>
                            <a href="{{ url('/collections?category=earrings') }}" class="block px-5 py-2.5 text-sm text-vrazio-forest hover:bg-vrazio-gold/5 hover:text-vrazio-gold transition-colors tracking-wide">Earrings</a>
                            <a href="{{ url('/collections?category=solitaires') }}" class="block px-5 py-2.5 text-sm text-vrazio-forest hover:bg-vrazio-gold/5 hover:text-vrazio-gold transition-colors tracking-wide">Solitaires</a>
                            <a href="{{ route('products.couture') }}" class="flex items-center justify-between px-5 py-2.5 text-sm text-vrazio-forest hover:bg-vrazio-gold/5 hover:text-vrazio-gold transition-colors tracking-wide bg-vrazio-gold/5 font-semibold">
                                <span>Haute Couture &amp; Clothes</span>
                                <span class="text-[9px] uppercase px-1.5 py-0.5 bg-[#9B1B30] text-white font-bold rounded-sm">New</span>
                            </a>
                            <div class="border-t border-vrazio-gold/10 mt-2 pt-2">
                                <a href="{{ route('products.index') ?? '#' }}" class="block px-5 py-2.5 text-sm font-medium text-vrazio-gold hover:bg-vrazio-gold/5 transition-colors tracking-wide">View All →</a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('products.couture') }}" class="text-sm font-medium tracking-widest uppercase text-vrazio-forest hover:text-vrazio-gold transition-colors py-2 flex items-center gap-1.5">
                        <span>Couture</span>
                        <span class="px-1.5 py-0.5 bg-[#9B1B30] text-white text-[9px] font-bold rounded-sm tracking-wider">New</span>
                    </a>
                    <a href="{{ url('/about') }}" class="text-sm font-medium tracking-widest uppercase text-vrazio-forest hover:text-vrazio-gold transition-colors py-2">About</a>
                    <a href="{{ url('/contact') }}" class="text-sm font-medium tracking-widest uppercase text-vrazio-forest hover:text-vrazio-gold transition-colors py-2">Contact</a>
                </div>

                {{-- Right Actions --}}
                <div class="flex items-center gap-3 lg:gap-4">
                    {{-- Search --}}
                    <button @click="searchOpen = !searchOpen"
                            class="p-2 text-vrazio-forest hover:text-vrazio-gold transition-colors"
                            aria-label="Search"
                            title="Search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    {{-- My Orders Link (Customer Orders Panel) --}}
                    <a href="{{ route('orders.index') }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 border border-vrazio-gold/30 hover:border-vrazio-gold bg-white hover:bg-vrazio-gold/10 text-vrazio-forest text-xs font-semibold tracking-wider transition-all rounded shadow-xs"
                       title="View My Orders & Warranty Claims">
                        <svg class="w-4 h-4 text-vrazio-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="hidden sm:inline">My Orders</span>
                    </a>

                    {{-- Customer Account / Sign In Link --}}
                    @auth
                        <a href="{{ route('account') }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 border border-vrazio-gold/40 hover:border-vrazio-gold bg-vrazio-gold/10 hover:bg-vrazio-gold/20 text-vrazio-forest text-xs font-semibold tracking-wider transition-all rounded shadow-xs"
                           title="Customer Account">
                            <svg class="w-4 h-4 text-[#0E261C] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="hidden sm:inline">{{ Str::words(auth()->user()->name, 1, '') }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 hover:border-vrazio-gold bg-white hover:bg-vrazio-gold/10 text-vrazio-forest text-xs font-semibold tracking-wider transition-all rounded shadow-xs"
                           title="Sign In or Register">
                            <svg class="w-4 h-4 text-vrazio-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="hidden sm:inline">Sign In</span>
                        </a>
                    @endauth

                    {{-- Shopping Cart (Direct Link & Live Badge) --}}
                    <a href="{{ route('cart.index') }}"
                       class="relative p-2 text-vrazio-forest hover:text-vrazio-gold transition-colors flex items-center"
                       aria-label="Shopping Cart"
                       title="View Shopping Cart">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        @livewire('cart-counter')
                    </a>
                </div>
            </div>

            {{-- Search Overlay --}}
            <div x-show="searchOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak
                 class="absolute inset-x-0 top-full bg-white shadow-lg border-t border-vrazio-gold/10 py-6 z-50">
                <div class="max-w-2xl mx-auto px-6">
                    <form action="{{ route('products.index') ?? '#' }}" method="GET" class="relative">
                        <input type="text" name="search"
                               placeholder="Search our collections..."
                               class="input-vrazio w-full pr-12 text-lg"
                               x-ref="searchInput"
                               @keydown.escape="searchOpen = false">
                        <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-vrazio-forest/50 hover:text-vrazio-gold transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </form>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-vrazio-forest/60">
                        <span>Popular:</span>
                        <a href="{{ url('/collections?search=solitaire+ring') }}" class="hover:text-vrazio-gold transition-colors">Solitaire Rings</a>
                        <span>·</span>
                        <a href="{{ url('/collections?search=diamond+necklace') }}" class="hover:text-vrazio-gold transition-colors">Diamond Necklaces</a>
                        <span>·</span>
                        <a href="{{ url('/collections?search=gold+bracelet') }}" class="hover:text-vrazio-gold transition-colors">Gold Bracelets</a>
                    </div>
                </div>
            </div>
        </nav>

        {{-- ═══════════════════════════════════════════════════════════════════
             CATEGORY TAGS & DIRECT NAVIGATION RIBBON
        ═══════════════════════════════════════════════════════════════════ --}}
        <div class="border-t border-vrazio-gold/15 bg-white/90 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 lg:px-6">
                <div class="flex items-center justify-between gap-4 py-2.5 overflow-x-auto no-scrollbar">
                    {{-- Category Navigation Pills --}}
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-nowrap whitespace-nowrap text-xs font-semibold uppercase tracking-wider">
                        <a href="{{ route('products.index') }}" 
                           class="px-3 py-1 rounded transition-colors {{ request()->routeIs('products.index') && !request('category') && !request('tag') ? 'bg-vrazio-forest text-vrazio-gold' : 'text-vrazio-forest/80 hover:text-vrazio-gold hover:bg-vrazio-gold/10' }}">
                            All Pieces
                        </a>
                        <span class="text-vrazio-gold/40">|</span>
                        <a href="{{ url('/collections?category=rings') }}" 
                           class="px-2.5 py-1 rounded transition-colors {{ request('category') === 'rings' ? 'bg-vrazio-forest text-vrazio-gold' : 'text-vrazio-forest/80 hover:text-vrazio-gold hover:bg-vrazio-gold/10' }}">
                            Rings
                        </a>
                        <a href="{{ url('/collections?category=necklaces') }}" 
                           class="px-2.5 py-1 rounded transition-colors {{ request('category') === 'necklaces' ? 'bg-vrazio-forest text-vrazio-gold' : 'text-vrazio-forest/80 hover:text-vrazio-gold hover:bg-vrazio-gold/10' }}">
                            Necklaces
                        </a>
                        <a href="{{ url('/collections?category=bracelets') }}" 
                           class="px-2.5 py-1 rounded transition-colors {{ request('category') === 'bracelets' ? 'bg-vrazio-forest text-vrazio-gold' : 'text-vrazio-forest/80 hover:text-vrazio-gold hover:bg-vrazio-gold/10' }}">
                            Bracelets
                        </a>
                        <a href="{{ url('/collections?category=earrings') }}" 
                           class="px-2.5 py-1 rounded transition-colors {{ request('category') === 'earrings' ? 'bg-vrazio-forest text-vrazio-gold' : 'text-vrazio-forest/80 hover:text-vrazio-gold hover:bg-vrazio-gold/10' }}">
                            Earrings
                        </a>
                        <a href="{{ url('/collections?category=solitaires') }}" 
                           class="px-2.5 py-1 rounded transition-colors {{ request('category') === 'solitaires' ? 'bg-vrazio-forest text-vrazio-gold' : 'text-vrazio-forest/80 hover:text-vrazio-gold hover:bg-vrazio-gold/10' }}">
                            Solitaires
                        </a>
                        <span class="text-vrazio-gold/40">|</span>
                        {{-- Dedicated Clothes & Couture Link with Highlight Badge --}}
                        <a href="{{ route('products.couture') }}" 
                           class="px-3 py-1 rounded flex items-center gap-1.5 transition-all {{ request()->routeIs('products.couture') || request('category') === 'couture' ? 'bg-vrazio-forest text-vrazio-gold border border-vrazio-gold/60 shadow-sm' : 'bg-vrazio-gold/15 text-vrazio-forest font-bold hover:bg-vrazio-gold/25 border border-vrazio-gold/30' }}">
                            <span>👗 Haute Couture &amp; Clothes</span>
                            <span class="px-1.5 py-0.2 bg-[#9B1B30] text-white text-[9px] font-bold rounded-sm uppercase tracking-widest">New</span>
                        </a>
                    </div>

                    {{-- Trending Product Tags (Desktop) --}}
                    <div class="hidden xl:flex items-center gap-1.5 text-[11px] text-vrazio-forest/70 whitespace-nowrap pl-4 border-l border-vrazio-gold/20">
                        <span class="text-vrazio-gold font-bold uppercase tracking-widest text-[10px]">Trending Tags:</span>
                        <a href="{{ url('/couture?tag=Bridal+Lehenga') }}" class="px-2 py-0.5 rounded bg-gray-100/80 hover:bg-vrazio-gold/20 hover:text-vrazio-forest transition-colors font-medium">#BridalLehenga</a>
                        <a href="{{ url('/couture?tag=Banarasi+Silk') }}" class="px-2 py-0.5 rounded bg-gray-100/80 hover:bg-vrazio-gold/20 hover:text-vrazio-forest transition-colors font-medium">#BanarasiSilk</a>
                        <a href="{{ url('/couture?tag=Royal+Sherwani') }}" class="px-2 py-0.5 rounded bg-gray-100/80 hover:bg-vrazio-gold/20 hover:text-vrazio-forest transition-colors font-medium">#RoyalSherwani</a>
                        <a href="{{ url('/collections?tag=GIA+Certified') }}" class="px-2 py-0.5 rounded bg-gray-100/80 hover:bg-vrazio-gold/20 hover:text-vrazio-forest transition-colors font-medium">#GIACertified</a>
                        <a href="{{ url('/collections?tag=Emerald') }}" class="px-2 py-0.5 rounded bg-gray-100/80 hover:bg-vrazio-gold/20 hover:text-vrazio-forest transition-colors font-medium">#Emerald</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════════════════════════════
         MOBILE MENU DRAWER
    ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
        {{-- Overlay --}}
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false"
             class="absolute inset-0 bg-vrazio-forest/60 backdrop-blur-sm"></div>

        {{-- Drawer --}}
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="absolute inset-y-0 left-0 w-80 max-w-[85vw] bg-vrazio-ivory shadow-2xl">

            <div class="flex flex-col h-full">
                {{-- Drawer Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-vrazio-gold/10">
                    <span class="font-heading text-xl font-bold tracking-[0.2em] text-vrazio-forest">
                        VR<span class="text-vrazio-gold">A</span>ZIO
                    </span>
                    <button @click="mobileMenuOpen = false" class="p-2 -mr-2 text-vrazio-forest/60 hover:text-vrazio-forest">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Drawer Links --}}
                <nav class="flex-1 overflow-y-auto py-4">
                    <div x-data="{ collectionsOpen: false }" class="px-4">
                        <button @click="collectionsOpen = !collectionsOpen"
                                class="w-full flex items-center justify-between px-3 py-3 text-sm font-medium tracking-widest uppercase text-vrazio-forest hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">
                            Collections
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': collectionsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="collectionsOpen" x-collapse x-cloak class="pl-4 mt-1 space-y-1">
                            <a href="{{ url('/collections?category=rings') }}" class="block px-3 py-2.5 text-sm text-vrazio-forest/80 hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">Rings</a>
                            <a href="{{ url('/collections?category=necklaces') }}" class="block px-3 py-2.5 text-sm text-vrazio-forest/80 hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">Necklaces</a>
                            <a href="{{ url('/collections?category=bracelets') }}" class="block px-3 py-2.5 text-sm text-vrazio-forest/80 hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">Bracelets</a>
                            <a href="{{ url('/collections?category=earrings') }}" class="block px-3 py-2.5 text-sm text-vrazio-forest/80 hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">Earrings</a>
                            <a href="{{ url('/collections?category=solitaires') }}" class="block px-3 py-2.5 text-sm text-vrazio-forest/80 hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">Solitaires</a>
                            <a href="{{ route('products.couture') }}" class="flex items-center justify-between px-3 py-2.5 text-sm font-semibold text-vrazio-forest hover:text-vrazio-gold transition-colors rounded-lg bg-vrazio-gold/10">
                                <span>👗 Haute Couture &amp; Clothes</span>
                                <span class="text-[9px] uppercase px-1.5 py-0.2 bg-[#9B1B30] text-white font-bold rounded-sm">New</span>
                            </a>
                            <a href="{{ route('products.index') ?? '#' }}" class="block px-3 py-2.5 text-sm font-medium text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">View All Jewellery →</a>
                        </div>
                    </div>
                    <div class="px-4 mt-1">
                        <a href="{{ route('products.couture') }}" class="flex items-center justify-between px-3 py-3 text-sm font-medium tracking-widest uppercase text-vrazio-forest hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">
                            <span>Haute Couture</span>
                            <span class="text-[9px] uppercase px-1.5 py-0.5 bg-[#9B1B30] text-white font-bold rounded-sm">New</span>
                        </a>
                        <a href="{{ url('/about') }}" class="block px-3 py-3 text-sm font-medium tracking-widest uppercase text-vrazio-forest hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">About</a>
                        <a href="{{ url('/contact') }}" class="block px-3 py-3 text-sm font-medium tracking-widest uppercase text-vrazio-forest hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">Contact</a>
                    </div>

                    {{-- Mobile Trending Tags --}}
                    <div class="px-7 py-3">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-vrazio-gold block mb-2">Trending Tags</span>
                        <div class="flex flex-wrap gap-1.5 text-xs">
                            <a href="{{ url('/couture?tag=Bridal+Lehenga') }}" class="px-2 py-0.5 rounded bg-white border border-vrazio-gold/20 text-vrazio-forest text-[11px]">#BridalLehenga</a>
                            <a href="{{ url('/couture?tag=Banarasi+Silk') }}" class="px-2 py-0.5 rounded bg-white border border-vrazio-gold/20 text-vrazio-forest text-[11px]">#BanarasiSilk</a>
                            <a href="{{ url('/couture?tag=Royal+Sherwani') }}" class="px-2 py-0.5 rounded bg-white border border-vrazio-gold/20 text-vrazio-forest text-[11px]">#RoyalSherwani</a>
                            <a href="{{ url('/collections?tag=GIA+Certified') }}" class="px-2 py-0.5 rounded bg-white border border-vrazio-gold/20 text-vrazio-forest text-[11px]">#GIACertified</a>
                        </div>
                    </div>

                    <div class="mx-6 my-2 border-t border-vrazio-gold/10"></div>

                    <div class="px-4 space-y-1">
                        @auth
                            <a href="{{ route('account') }}" class="flex items-center justify-between px-3 py-2.5 text-sm text-vrazio-forest font-semibold hover:text-vrazio-gold transition-colors rounded-lg bg-vrazio-gold/10">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>My Account ({{ Str::words(auth()->user()->name, 1, '') }})</span>
                                </div>
                                <span class="text-xs text-[#D4AF37]">&rarr;</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="px-3 pt-1">
                                @csrf
                                <button type="submit" class="w-full text-left text-xs text-red-600 hover:text-red-800 font-semibold py-1">
                                    Sign Out
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-vrazio-forest font-medium hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Sign In / Register
                            </a>
                        @endauth
                        <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-vrazio-forest/70 hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">
                            <svg class="w-4 h-4 text-vrazio-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            My Orders &amp; Warranty
                        </a>
                        <a href="{{ route('orders.track') ?? '#' }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-vrazio-forest/70 hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Track Order
                        </a>
                        <a href="{{ route('faq') ?? '#' }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-vrazio-forest/70 hover:text-vrazio-gold transition-colors rounded-lg hover:bg-vrazio-gold/5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Help & FAQ
                        </a>
                    </div>
                </nav>

                {{-- Drawer Footer --}}
                <div class="px-6 py-4 border-t border-vrazio-gold/10 space-y-3">
                    <a href="tel:+919876543210" class="flex items-center gap-2 text-xs text-vrazio-forest/60 hover:text-vrazio-gold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        +91 98765 43210
                    </a>
                    <a href="mailto:concierge@vrazio.com" class="flex items-center gap-2 text-xs text-vrazio-forest/60 hover:text-vrazio-gold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        concierge@vrazio.com
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════════════════════════════ --}}
    <main>
        @yield('content')
    </main>

    {{-- ═══════════════════════════════════════════════════════════════════
         CART DRAWER
    ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="cartDrawerOpen" x-cloak class="fixed inset-0 z-50">
        {{-- Overlay --}}
        <div x-show="cartDrawerOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="cartDrawerOpen = false"
             class="absolute inset-0 bg-vrazio-forest/60 backdrop-blur-sm"></div>

        {{-- Drawer --}}
        <div x-show="cartDrawerOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 w-96 max-w-[90vw] bg-vrazio-ivory shadow-2xl flex flex-col"
             x-data="{
                 cartItems: JSON.parse(localStorage.getItem('vrazio_cart') || '[]'),
                 get subtotal() {
                     return this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                 },
                 get itemCount() {
                     return this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
                 },
                 removeItem(index) {
                     this.cartItems.splice(index, 1);
                     localStorage.setItem('vrazio_cart', JSON.stringify(this.cartItems));
                     window.dispatchEvent(new CustomEvent('cart-updated'));
                 },
                 updateQty(index, delta) {
                     let newQty = this.cartItems[index].quantity + delta;
                     if (newQty < 1) return;
                     if (newQty > 10) return;
                     this.cartItems[index].quantity = newQty;
                     localStorage.setItem('vrazio_cart', JSON.stringify(this.cartItems));
                     window.dispatchEvent(new CustomEvent('cart-updated'));
                 },
                 formatPrice(amount) {
                     return '₹' + amount.toLocaleString('en-IN');
                 }
             }">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-vrazio-gold/10">
                <h2 class="font-heading text-lg font-semibold text-vrazio-forest tracking-wide">
                    Shopping Bag
                    <span class="text-sm font-body font-normal text-vrazio-forest/50" x-text="'(' + itemCount + ')'"></span>
                </h2>
                <button @click="cartDrawerOpen = false" class="p-2 -mr-2 text-vrazio-forest/60 hover:text-vrazio-forest transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto px-6 py-4">
                {{-- Empty State --}}
                <template x-if="cartItems.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center py-12">
                        <svg class="w-16 h-16 text-vrazio-gold/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <p class="font-heading text-lg text-vrazio-forest/60 mb-2">Your bag is empty</p>
                        <p class="text-sm text-vrazio-forest/40 mb-6">Discover our exquisite collections</p>
                        <a href="{{ route('products.index') ?? '#' }}" @click="cartDrawerOpen = false" class="btn-vrazio text-sm">
                            Explore Collections
                        </a>
                    </div>
                </template>

                {{-- Items List --}}
                <template x-for="(item, index) in cartItems" :key="index">
                    <div class="flex gap-4 py-4 border-b border-vrazio-gold/5 last:border-0">
                        <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-white">
                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-heading text-sm font-semibold text-vrazio-forest truncate" x-text="item.title"></h4>
                            <p class="text-xs text-vrazio-forest/50 mt-0.5" x-text="item.metal_type"></p>
                            <p class="text-sm font-semibold text-vrazio-gold mt-1" x-text="formatPrice(item.price)"></p>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center border border-vrazio-gold/20 rounded-md">
                                    <button @click="updateQty(index, -1)" class="px-2 py-1 text-vrazio-forest/60 hover:text-vrazio-gold transition-colors text-xs">−</button>
                                    <span class="px-2 py-1 text-xs font-medium border-x border-vrazio-gold/20" x-text="item.quantity"></span>
                                    <button @click="updateQty(index, 1)" class="px-2 py-1 text-vrazio-forest/60 hover:text-vrazio-gold transition-colors text-xs">+</button>
                                </div>
                                <button @click="removeItem(index)" class="text-vrazio-ruby/60 hover:text-vrazio-ruby transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <template x-if="cartItems.length > 0">
                <div class="border-t border-vrazio-gold/10 px-6 py-5 space-y-4 bg-white/50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-vrazio-forest/70">Subtotal</span>
                        <span class="font-heading text-lg font-bold text-vrazio-forest" x-text="formatPrice(subtotal)"></span>
                    </div>
                    <p class="text-xs text-vrazio-forest/40">GST & shipping calculated at checkout</p>
                    <a href="{{ url('/checkout') }}" class="btn-vrazio w-full text-center block">
                        Proceed to Checkout
                    </a>
                    <button @click="cartDrawerOpen = false" class="w-full text-center text-sm text-vrazio-forest/60 hover:text-vrazio-gold transition-colors py-1">
                        Continue Shopping
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         OTP VERIFICATION MODAL
    ═══════════════════════════════════════════════════════════════════ --}}
    <div x-data="{
             show: false,
             phone: '',
             otp: ['', '', '', '', '', ''],
             step: 'phone',
             loading: false,
             error: '',
             countdown: 0,
             countdownInterval: null,
             verified: false,
             sendOtp() {
                 if (!this.phone || this.phone.length < 10) {
                     this.error = 'Please enter a valid 10-digit phone number';
                     return;
                 }
                 this.loading = true;
                 this.error = '';
                 // Simulate API call
                 setTimeout(() => {
                     this.loading = false;
                     this.step = 'otp';
                     this.startCountdown();
                 }, 1500);
             },
             verifyOtp() {
                 let code = this.otp.join('');
                 if (code.length < 6) {
                     this.error = 'Please enter the complete 6-digit OTP';
                     return;
                 }
                 this.loading = true;
                 this.error = '';
                 setTimeout(() => {
                     this.loading = false;
                     this.verified = true;
                     this.step = 'verified';
                     window.dispatchEvent(new CustomEvent('otp-verified', { detail: { phone: this.phone } }));
                     setTimeout(() => { this.show = false; }, 1500);
                 }, 1500);
             },
             startCountdown() {
                 this.countdown = 30;
                 this.countdownInterval = setInterval(() => {
                     this.countdown--;
                     if (this.countdown <= 0) clearInterval(this.countdownInterval);
                 }, 1000);
             },
             resendOtp() {
                 if (this.countdown > 0) return;
                 this.otp = ['', '', '', '', '', ''];
                 this.error = '';
                 this.sendOtp();
             },
             handleOtpInput(index, event) {
                 let val = event.target.value;
                 if (val && index < 5) {
                     this.$refs['otp' + (index + 1)].focus();
                 }
             },
             handleOtpKeydown(index, event) {
                 if (event.key === 'Backspace' && !this.otp[index] && index > 0) {
                     this.$refs['otp' + (index - 1)].focus();
                 }
             },
             reset() {
                 this.step = 'phone';
                 this.otp = ['', '', '', '', '', ''];
                 this.error = '';
                 this.loading = false;
                 this.verified = false;
                 if (this.countdownInterval) clearInterval(this.countdownInterval);
                 this.countdown = 0;
             }
         }"
         @open-otp-modal.window="show = true; phone = $event.detail?.phone || ''; reset(); if(phone) step = 'phone';"
         @keydown.escape.window="show = false"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center">

        {{-- Overlay --}}
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="show = false"
             class="absolute inset-0 bg-vrazio-forest/70 backdrop-blur-sm"></div>

        {{-- Modal --}}
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-vrazio-ivory rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 border border-vrazio-gold/10">

            {{-- Close --}}
            <button @click="show = false" class="absolute top-4 right-4 text-vrazio-forest/40 hover:text-vrazio-forest transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Phone Step --}}
            <template x-if="step === 'phone'">
                <div class="text-center">
                    <div class="w-16 h-16 bg-vrazio-gold/10 rounded-full flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-vrazio-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-xl font-semibold text-vrazio-forest mb-2">Verify Phone Number</h3>
                    <p class="text-sm text-vrazio-forest/60 mb-6">We'll send you a one-time verification code</p>

                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex items-center gap-1 px-3 py-3 bg-white border border-vrazio-gold/20 rounded-lg text-sm text-vrazio-forest/70">
                            🇮🇳 +91
                        </span>
                        <input type="tel" x-model="phone"
                               maxlength="10"
                               placeholder="Enter mobile number"
                               class="input-vrazio flex-1"
                               @keydown.enter="sendOtp()">
                    </div>

                    <p x-show="error" x-text="error" class="text-xs text-vrazio-ruby mb-3"></p>

                    <button @click="sendOtp()" :disabled="loading"
                            class="btn-vrazio w-full flex items-center justify-center gap-2">
                        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="loading ? 'Sending...' : 'Send OTP'"></span>
                    </button>
                </div>
            </template>

            {{-- OTP Step --}}
            <template x-if="step === 'otp'">
                <div class="text-center">
                    <div class="w-16 h-16 bg-vrazio-gold/10 rounded-full flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-vrazio-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-xl font-semibold text-vrazio-forest mb-2">Enter OTP</h3>
                    <p class="text-sm text-vrazio-forest/60 mb-6">Code sent to +91 <span x-text="phone"></span></p>

                    <div class="flex justify-center gap-2 mb-4">
                        <template x-for="(digit, index) in otp" :key="index">
                            <input type="text"
                                   maxlength="1"
                                   :x-ref="'otp' + index"
                                   x-model="otp[index]"
                                   @input="handleOtpInput(index, $event)"
                                   @keydown="handleOtpKeydown(index, $event)"
                                   class="w-12 h-14 text-center text-xl font-semibold bg-white border-2 border-vrazio-gold/20 rounded-lg focus:border-vrazio-gold focus:ring-2 focus:ring-vrazio-gold/20 outline-none transition-all text-vrazio-forest">
                        </template>
                    </div>

                    <p x-show="error" x-text="error" class="text-xs text-vrazio-ruby mb-3"></p>

                    <button @click="verifyOtp()" :disabled="loading"
                            class="btn-vrazio w-full flex items-center justify-center gap-2 mb-4">
                        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="loading ? 'Verifying...' : 'Verify OTP'"></span>
                    </button>

                    <p class="text-sm text-vrazio-forest/50">
                        <template x-if="countdown > 0">
                            <span>Resend in <span x-text="countdown" class="font-semibold text-vrazio-gold"></span>s</span>
                        </template>
                        <template x-if="countdown <= 0">
                            <button @click="resendOtp()" class="text-vrazio-gold hover:text-vrazio-dark-gold font-medium transition-colors">Resend OTP</button>
                        </template>
                    </p>
                </div>
            </template>

            {{-- Verified Step --}}
            <template x-if="step === 'verified'">
                <div class="text-center py-4">
                    <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-5">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" class="checkmark-animate"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-xl font-semibold text-vrazio-forest mb-2">Verified!</h3>
                    <p class="text-sm text-vrazio-forest/60">Your phone number has been verified successfully</p>
                </div>
            </template>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════════════════════════ --}}
    <footer class="bg-vrazio-forest text-white/80">
        {{-- Main Footer --}}
        <div class="max-w-7xl mx-auto px-6 py-16 lg:py-20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12">

                {{-- About VRAZIO --}}
                {{-- About VRAZIO --}}
                <div class="lg:col-span-1">
                    <a href="{{ url('/') }}" class="inline-block mb-5">
                        <span class="font-heading text-2xl font-bold tracking-[0.2em] text-white">
                            VR<span class="text-vrazio-gold">A</span>ZIO
                        </span>
                    </a>
                    <p class="text-sm leading-relaxed text-white/70 mb-5">
                        Fine Jewellery and Couture crafted with care and passion. Every piece brings together beauty, certified quality, and timeless style.
                    </p>
                    <div class="flex items-center gap-3">
                        {{-- Instagram --}}
                        <a href="https://www.instagram.com/vrazio.official/" target="_blank" rel="noopener noreferrer" 
                           class="w-9 h-9 rounded-full border border-white/20 flex items-center justify-center hover:border-vrazio-gold hover:text-vrazio-gold transition-all" 
                           aria-label="Instagram @vrazio.official" title="Instagram @vrazio.official">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        {{-- Facebook --}}
                        <a href="https://www.facebook.com/share/1CJBJZbnkG/" target="_blank" rel="noopener noreferrer" 
                           class="w-9 h-9 rounded-full border border-white/20 flex items-center justify-center hover:border-vrazio-gold hover:text-vrazio-gold transition-all" 
                           aria-label="Facebook Page" title="Facebook Page">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        {{-- WhatsApp --}}
                        <a href="https://wa.me/918979622310?text=Hi%20VRAZIO%2C%20I%20have%20a%20query" target="_blank" rel="noopener noreferrer" 
                           class="w-9 h-9 rounded-full border border-emerald-500/40 text-emerald-400 flex items-center justify-center hover:border-emerald-400 hover:bg-emerald-500/10 transition-all" 
                           aria-label="WhatsApp Support" title="Chat on WhatsApp">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.761.821 2.796.821h.005c3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.773-5.768zm7.135 5.769c-.002 3.935-3.201 7.134-7.136 7.134-1.257 0-2.434-.332-3.468-.944l-4.562 1.196 1.217-4.444c-.672-1.076-1.027-2.327-1.026-3.61.002-3.935 3.202-7.135 7.137-7.135 3.935 0 7.138 3.2 7.138 7.133z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h4 class="font-heading text-sm font-semibold tracking-widest uppercase text-vrazio-gold mb-5">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ url('/collections?category=rings') }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Rings</a></li>
                        <li><a href="{{ url('/collections?category=necklaces') }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Necklaces</a></li>
                        <li><a href="{{ url('/collections?category=bracelets') }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Bracelets</a></li>
                        <li><a href="{{ url('/collections?category=earrings') }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Earrings</a></li>
                        <li><a href="{{ url('/collections?category=solitaires') }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Solitaires</a></li>
                        <li><a href="{{ route('products.couture') }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Haute Couture &amp; Clothes</a></li>
                        <li><a href="{{ url('/about') }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">About Us</a></li>
                    </ul>
                </div>

                {{-- Customer Care --}}
                <div>
                    <h4 class="font-heading text-sm font-semibold tracking-widest uppercase text-vrazio-gold mb-5">Customer Care</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('orders.index') }}" class="text-sm text-vrazio-gold font-medium hover:underline transition-colors">My Orders &amp; Claims</a></li>
                        <li><a href="{{ route('orders.track') ?? '#' }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Track Order</a></li>
                        <li><a href="{{ route('warranty-policy') ?? '#' }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Warranty Policy</a></li>
                        <li><a href="{{ route('shipping-policy') ?? '#' }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Shipping &amp; Delivery</a></li>
                        <li><a href="{{ route('returns-policy') ?? '#' }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Returns &amp; Exchanges</a></li>
                        <li><a href="{{ route('faq') ?? '#' }}" class="text-sm text-white/70 hover:text-vrazio-gold transition-colors">Help &amp; FAQ</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h4 class="font-heading text-sm font-semibold tracking-widest uppercase text-vrazio-gold mb-5">Contact Us</h4>
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.761.821 2.796.821h.005c3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.773-5.768zm7.135 5.769c-.002 3.935-3.201 7.134-7.136 7.134-1.257 0-2.434-.332-3.468-.944l-4.562 1.196 1.217-4.444c-.672-1.076-1.027-2.327-1.026-3.61.002-3.935 3.202-7.135 7.137-7.135 3.935 0 7.138 3.2 7.138 7.133z"/></svg>
                            <a href="https://wa.me/918979622310?text=Hi%20VRAZIO%2C%20I%20have%20a%20query" target="_blank" class="text-sm text-emerald-400 font-semibold hover:underline">
                                WhatsApp: +91 89796 22310
                            </a>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-vrazio-gold flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:+918979622310" class="text-sm text-white/80 hover:text-vrazio-gold transition-colors">+91 89796 22310</a>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-vrazio-gold flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:vrazioofficial@gmail.com" class="text-sm text-white/80 hover:text-vrazio-gold transition-colors">vrazioofficial@gmail.com</a>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-vrazio-gold flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-white/60">Monday — Saturday: 10:00 AM — 8:00 PM IST</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="border-t border-white/10">
            <div class="max-w-7xl mx-auto px-6 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs text-white/50">
                        <span>© {{ date('Y') }} VRAZIO Haute Joaillerie. All rights reserved.</span>
                        <span class="text-white/20">•</span>
                        <a href="{{ route('pages.privacy') }}" class="hover:text-vrazio-gold transition-colors">Privacy &amp; Cookies</a>
                        <span class="text-white/20">•</span>
                        <a href="{{ route('pages.terms') }}" class="hover:text-vrazio-gold transition-colors">Terms of Service</a>
                        <span class="text-white/20">•</span>
                        <a href="{{ route('sitemap') }}" target="_blank" class="hover:text-vrazio-gold transition-colors">Sitemap</a>
                    </div>
                    <div class="flex items-center gap-4">
                        {{-- BIS Hallmark Badge --}}
                        <div class="flex items-center gap-1.5 text-xs text-white/50">
                            <svg class="w-4 h-4 text-vrazio-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            BIS Hallmarked
                        </div>

                        <span class="text-white/20">|</span>

                        {{-- Payment Icons --}}
                        <div class="flex items-center gap-2 text-xs text-white/50">
                            <span>Visa</span>
                            <span>Mastercard</span>
                            <span>UPI</span>
                        </div>

                        <span class="text-white/20">|</span>

                        {{-- Razorpay Badge --}}
                        <div class="flex items-center gap-1.5 text-xs text-white/50">
                            <svg class="w-4 h-4 text-vrazio-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Secured by Razorpay
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- Luxury Cookie Consent Banner & Dynamic WhatsApp Float --}}
    <div x-data="{
            showCookieBanner: false,
            init() {
                try {
                    this.showCookieBanner = !localStorage.getItem('vrazio_cookie_consent');
                } catch(e) {
                    this.showCookieBanner = false;
                }
            },
            acceptCookies() {
                try {
                    localStorage.setItem('vrazio_cookie_consent', 'accepted');
                } catch(e) {}
                this.showCookieBanner = false;
                if (typeof initGA4 === 'function') {
                    initGA4();
                }
            }
         }">

        {{-- Luxury Cookie Consent Banner --}}
        <div x-show="showCookieBanner" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-full opacity-0"
             x-cloak
             class="fixed bottom-0 inset-x-0 z-50 p-4 sm:p-6 bg-[#0E261C]/95 backdrop-blur-md border-t border-[#D4AF37]/30 text-white shadow-2xl">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-xs text-[#FAF8F5]/90 max-w-3xl">
                    <svg class="w-6 h-6 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <p>
                        <strong>Atelier Cookie Protocol:</strong> VRAZIO curates an elevated bespoke experience using necessary and performance cookies for shopping bag persistence and security. By browsing our atelier, you agree to our 
                        <a href="{{ route('pages.privacy') }}#cookies" class="text-[#D4AF37] underline hover:text-[#F3E5AB]">Cookie &amp; Privacy Policy</a>.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <button type="button" 
                            @click="acceptCookies()" 
                            class="px-6 py-2.5 bg-[#D4AF37] text-[#0E261C] font-semibold text-xs uppercase tracking-widest hover:bg-[#F3E5AB] transition-all shadow-md">
                        Accept Protocol
                    </button>
                </div>
            </div>
        </div>

        {{-- Floating WhatsApp Quick Connect Button (Dynamically raised when cookie banner is open) --}}
        <aside aria-label="Customer Support" 
               :class="showCookieBanner ? 'bottom-24 sm:bottom-24' : 'bottom-4 sm:bottom-6'"
               style="position: fixed; right: 16px; bottom: 16px; z-index: 50;"
               class="fixed right-4 sm:right-6 z-50 transition-all duration-300">
            <a href="https://wa.me/918979622310?text=Hi%20VRAZIO%2C%20I%20have%20a%20query%20about%20an%20order%20or%20warranty" 
               target="_blank" 
               rel="noopener noreferrer"
               style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background-color: #25D366; color: #ffffff; border-radius: 9999px; text-decoration: none; box-shadow: 0 10px 25px rgba(0,0,0,0.3);"
               class="group flex items-center justify-center p-3 sm:px-4 sm:py-3 bg-[#25D366] hover:bg-[#1ebd5b] text-white rounded-full shadow-2xl transition-all duration-300 hover:scale-105"
               title="Chat with VRAZIO on WhatsApp">
                <svg width="24" height="24" style="width: 24px; height: 24px; min-width: 24px; max-width: 24px; display: block;" class="w-5 h-5 sm:w-6 sm:h-6 fill-current" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.761.821 2.796.821h.005c3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.773-5.768zm7.135 5.769c-.002 3.935-3.201 7.134-7.136 7.134-1.257 0-2.434-.332-3.468-.944l-4.562 1.196 1.217-4.444c-.672-1.076-1.027-2.327-1.026-3.61.002-3.935 3.202-7.135 7.137-7.135 3.935 0 7.138 3.2 7.138 7.133z"/>
                </svg>
                <span class="text-xs font-bold tracking-wide hidden sm:inline" style="font-size: 12px; font-weight: bold;">WhatsApp Support</span>
            </a>
        </aside>
    </div>

    {{-- Livewire Scripts --}}
    @livewireScripts

    @stack('scripts')
</body>
</html>
