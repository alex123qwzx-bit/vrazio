@props(['membership'])

@php
    $tier = strtolower($membership->tier ?? 'member');
    $tierName = $membership->tier_name ?? ucfirst($tier);
    $memberId = $membership->member_id;
    $validUntil = $membership->expires_at ? $membership->expires_at->format('M Y') : '12M';
    $since = $membership->starts_at ? $membership->starts_at->format('Y') : date('Y');
    $patronName = $membership->user->name ?? 'VRAZIO Patron';
    $verifyUrl = route('prive-club.verify', $memberId);
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($verifyUrl) . '&bgcolor=ffffff&color=0E261C&margin=1';

    // Tier specific gradients & border styles
    $cardTheme = match($tier) {
        'gold' => [
            'bg' => 'bg-gradient-to-br from-[#201A09] via-[#4D3A14] to-[#120E05]',
            'border' => 'border border-[#D4AF37] shadow-[0_20px_50px_rgba(212,175,55,0.25)]',
            'accent' => 'text-[#F3E5AB]',
            'badge_bg' => 'bg-[#D4AF37] text-[#0E261C]',
            'chip' => 'from-[#FFE27D] to-[#D4AF37]',
        ],
        'gold_max' => [
            'bg' => 'bg-gradient-to-br from-[#0A0A0A] via-[#1F1B16] to-[#050505]',
            'border' => 'border-2 border-[#D4AF37] shadow-[0_25px_60px_rgba(0,0,0,0.6)]',
            'accent' => 'text-[#F3E5AB]',
            'badge_bg' => 'bg-gradient-to-r from-[#D4AF37] to-[#F3E5AB] text-[#0A0A0A]',
            'chip' => 'from-[#FFF1C5] to-[#B8860B]',
        ],
        default => [
            'bg' => 'bg-gradient-to-br from-[#0E261C] via-[#16382B] to-[#081711]',
            'border' => 'border border-[#D4AF37]/50 shadow-[0_15px_40px_rgba(14,38,28,0.3)]',
            'accent' => 'text-[#D4AF37]',
            'badge_bg' => 'bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37]',
            'chip' => 'from-[#D4AF37] to-[#8C7320]',
        ]
    };
@endphp

<div class="relative w-full max-w-md mx-auto aspect-[1.586/1] rounded-2xl p-6 sm:p-7 {{ $cardTheme['bg'] }} {{ $cardTheme['border'] }} text-white overflow-hidden transition-all duration-500 hover:scale-[1.02]">
    {{-- Background Watermark / Hologram Pattern --}}
    <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-[#D4AF37]/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-[#D4AF37]/5 blur-3xl pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:16px_16px] opacity-10 pointer-events-none"></div>

    <div class="relative h-full flex flex-col justify-between z-10">
        {{-- Card Top Header --}}
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full border border-[#D4AF37]/60 flex items-center justify-center bg-[#0E261C] shadow-xs">
                    <span class="font-heading text-xs font-bold text-[#D4AF37]">V</span>
                </div>
                <div>
                    <span class="font-heading text-lg font-bold tracking-[0.25em] text-white block leading-none">
                        VR<span class="text-[#D4AF37]">A</span>ZIO
                    </span>
                    <span class="text-[9px] uppercase tracking-[0.3em] text-[#D4AF37]/80 block mt-0.5">
                        Privé Club
                    </span>
                </div>
            </div>

            {{-- Tier Badge --}}
            <span class="text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full {{ $cardTheme['badge_bg'] }} shadow-xs">
                {{ $tierName }}
            </span>
        </div>

        {{-- Smart Chip & QR Code --}}
        <div class="flex items-center justify-between my-auto pt-2">
            {{-- Atelier Metallic Smart Chip Graphic --}}
            <div class="w-11 h-8 rounded-md bg-gradient-to-br {{ $cardTheme['chip'] }} p-0.5 shadow-inner flex flex-col justify-between overflow-hidden opacity-90">
                <div class="h-1.5 border-b border-black/20"></div>
                <div class="h-1.5 border-b border-black/20 flex gap-1">
                    <div class="w-2 border-r border-black/20"></div>
                    <div class="flex-1"></div>
                </div>
                <div class="h-1.5"></div>
            </div>

            {{-- 100% Digital Authentication QR Code --}}
            <a href="{{ $verifyUrl }}" target="_blank" title="Scan or tap to verify on VRAZIO Ledger" class="p-1 bg-white rounded-md shadow-md block hover:scale-105 transition-transform group">
                <img src="{{ $qrUrl }}" alt="Verify {{ $memberId }}" class="w-12 h-12 rounded object-contain">
            </a>
        </div>

        {{-- Card Bottom Info --}}
        <div>
            <div class="font-mono text-sm sm:text-base font-bold tracking-[0.18em] {{ $cardTheme['accent'] }} drop-shadow-xs mb-2">
                {{ $memberId }}
            </div>

            <div class="flex items-end justify-between text-[10px] tracking-wider uppercase text-gray-300">
                <div>
                    <span class="text-[8px] text-gray-400 block tracking-widest">Patron</span>
                    <span class="font-semibold text-white tracking-wider truncate max-w-[170px] block">{{ $patronName }}</span>
                </div>
                <div class="text-right">
                    <span class="text-[8px] text-gray-400 block tracking-widest">Valid Thru</span>
                    <span class="font-mono font-bold text-white">{{ $validUntil }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
