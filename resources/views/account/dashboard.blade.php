@extends('layouts.app')

@section('title', 'My Account | VRAZIO')
@section('meta_description', 'Manage your VRAZIO account, view past orders, track deliveries, and check warranty claim status.')

@section('content')
<div x-data="{ editModal: {{ ($errors->any() || request()->has('edit')) ? 'true' : 'false' }} }" class="bg-[#FAF8F5] min-h-screen py-10 md:py-14">
    <div class="max-w-6xl mx-auto px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'Home' => route('home'),
            'My Account' => ''
        ]" />

        {{-- Notifications --}}
        @if(session('success'))
            <div class="my-6 p-4 bg-green-50 border-l-4 border-green-600 text-green-800 text-xs sm:text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="my-6 p-4 bg-amber-50 border-l-4 border-[#D4AF37] text-amber-900 text-xs sm:text-sm">
                {{ session('info') }}
            </div>
        @endif

        {{-- Verification Banner if Email is NOT verified --}}
        @if(!$user->hasVerifiedEmail())
            <div class="my-6 p-4 sm:p-5 bg-amber-50 border border-amber-300 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-800 flex items-center justify-center shrink-0 mt-0.5">
                        <svg width="16" height="16" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900">Your email address is not verified yet</h4>
                        <p class="text-xs text-amber-750 text-amber-800 mt-0.5">
                            Please verify <span class="font-semibold">{{ $user->email }}</span> to secure your account and receive warranty updates.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('verification.notice') }}" 
                       class="px-4 py-2 bg-[#0E261C] text-[#D4AF37] text-xs font-semibold uppercase tracking-wider hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors rounded-xs">
                        Verify Now
                    </a>
                </div>
            </div>
        @endif

        {{-- Account Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between my-8 gap-4 pb-6 border-b border-gray-200">
            <div>
                <span class="text-[11px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">
                    Customer Account
                </span>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-1">
                    Hello, {{ $user->name }}
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Manage your personal details, track your orders, and claim warranty support.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" 
                        @click="$dispatch('open-edit-profile')"
                        onclick="window.dispatchEvent(new CustomEvent('open-edit-profile'))"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#0E261C] text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-wider rounded-xs transition-colors shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit Profile
                </button>
                <a href="https://wa.me/918791227210?text={{ urlencode('Hello VRAZIO Support, I need help with my account (Name: ' . $user->name . ')') }}" 
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#25D366] text-white text-xs font-semibold uppercase tracking-wider hover:bg-[#1EBE5D] transition-colors rounded-xs shadow-xs">
                    <svg width="16" height="16" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.102-.115.434-.506.549-.68.116-.173.232-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/>
                    </svg>
                    WhatsApp Help
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2.5 bg-gray-200 text-gray-700 text-xs font-semibold uppercase tracking-wider hover:bg-[#9B1B30] hover:text-white transition-colors rounded-xs">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>

        {{-- 2-Column Dashboard Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Account Details & Verification Cards --}}
            <div class="space-y-6">
                {{-- Profile Info Card --}}
                <div class="bg-white border border-[#D4AF37]/30 p-6 rounded-xs shadow-xs">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                        <h2 class="text-xs uppercase tracking-[0.2em] text-[#0E261C] font-bold">
                            Profile Information
                        </h2>
                        <button type="button" 
                                @click="$dispatch('open-edit-profile')"
                                onclick="window.dispatchEvent(new CustomEvent('open-edit-profile'))"
                                class="inline-flex items-center gap-1 text-[11px] text-[#D4AF37] hover:text-[#0E261C] font-semibold uppercase tracking-wider transition-colors cursor-pointer">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            Edit
                        </button>
                    </div>
                    <div class="space-y-3.5 text-xs">
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Full Name</span>
                            <span class="font-semibold text-gray-900 text-sm">{{ $user->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Email Address</span>
                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                <span class="font-medium text-gray-800 break-all">{{ $user->email }}</span>
                                @if($user->hasVerifiedEmail())
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded-full border border-green-200 shrink-0">
                                        <svg width="20" height="20" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200 shrink-0">
                                        Unverified
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div x-data="{
                            open: false,
                            phone: '{{ $user->phone }}',
                            otp: '',
                            otpSent: false,
                            loading: false,
                            msg: '',
                            isError: false,
                            devOtp: '',
                            timer: 0,
                            interval: null,

                            sendSms() {
                                let clean = (this.phone || '').replace(/\D/g, '');
                                if (clean.length < 10) {
                                    this.msg = 'Please enter a valid 10-digit mobile number';
                                    this.isError = true;
                                    return;
                                }
                                this.loading = true;
                                this.msg = '';
                                fetch('{{ route('account.phone.verify') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ action: 'send', phone: clean })
                                })
                                .then(r => r.json())
                                .then(d => {
                                    this.loading = false;
                                    if (d.success) {
                                        this.otpSent = true;
                                        this.msg = d.message || 'OTP sent via SMS!';
                                        this.isError = false;
                                        if (d.dev_otp) {
                                            this.devOtp = d.dev_otp;
                                            this.otp = d.dev_otp;
                                        }
                                        this.timer = 30;
                                        if (this.interval) clearInterval(this.interval);
                                        this.interval = setInterval(() => {
                                            if (this.timer > 0) this.timer--;
                                            else clearInterval(this.interval);
                                        }, 1000);
                                    } else {
                                        this.msg = d.message || 'Error sending OTP';
                                        this.isError = true;
                                    }
                                })
                                .catch(() => {
                                    this.loading = false;
                                    this.msg = 'Network error. Try again.';
                                    this.isError = true;
                                });
                            },

                            verifySms() {
                                if (!this.otp || this.otp.length !== 6) {
                                    this.msg = 'Enter 6-digit OTP';
                                    this.isError = true;
                                    return;
                                }
                                this.loading = true;
                                this.msg = '';
                                fetch('{{ route('account.phone.verify') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ action: 'verify', otp: this.otp })
                                })
                                .then(r => r.json())
                                .then(d => {
                                    this.loading = false;
                                    if (d.success) {
                                        window.location.reload();
                                    } else {
                                        this.msg = d.message || 'Invalid OTP';
                                        this.isError = true;
                                    }
                                })
                                .catch(() => {
                                    this.loading = false;
                                    this.msg = 'Network error. Try again.';
                                    this.isError = true;
                                });
                            }
                        }">
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Phone Number</span>
                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                <span class="font-medium text-gray-800">{{ $user->phone ?? 'Not provided' }}</span>
                                @if($user->phone_verified_at)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded-full border border-green-200 shrink-0">
                                        <svg width="20" height="20" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Verified
                                    </span>
                                @else
                                    <button type="button" 
                                            @click="open = !open" 
                                            class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#0E261C] bg-[#D4AF37]/20 hover:bg-[#D4AF37]/40 border border-[#D4AF37] px-2.5 py-0.5 rounded-xs transition-colors shrink-0">
                                        Verify with SMS
                                    </button>
                                @endif
                            </div>

                            {{-- Inline Phone Verification Form --}}
                            @if(!$user->phone_verified_at)
                                <div x-show="open" x-cloak class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-xs space-y-2.5 text-xs">
                                    <div x-show="msg" :class="isError ? 'text-red-600 bg-red-50 p-2' : 'text-emerald-700 bg-emerald-50 p-2'" x-text="msg" class="rounded-xs"></div>
                                    <div x-show="devOtp" class="text-amber-800 bg-amber-50 p-2 border border-[#D4AF37] rounded-xs font-mono font-bold">
                                        Dev OTP: <span x-text="devOtp"></span>
                                    </div>

                                    <div x-show="!otpSent" class="space-y-2">
                                        <input type="tel" x-model="phone" placeholder="10-digit phone" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xs">
                                        <button type="button" @click="sendSms()" :disabled="loading" class="w-full py-1.5 bg-[#0E261C] text-[#D4AF37] font-semibold text-[11px] rounded-xs">
                                            <span x-show="!loading">Send Verification SMS</span>
                                            <span x-show="loading">Sending...</span>
                                        </button>
                                    </div>

                                    <div x-show="otpSent" class="space-y-2">
                                        <input type="text" x-model="otp" placeholder="Enter 6-digit OTP" maxlength="6" class="w-full text-center tracking-widest font-mono text-sm py-1.5 border border-[#D4AF37] rounded-xs">
                                        <button type="button" @click="verifySms()" :disabled="loading" class="w-full py-1.5 bg-[#0E261C] text-[#D4AF37] font-semibold text-[11px] rounded-xs">
                                            <span x-show="!loading">Confirm OTP</span>
                                            <span x-show="loading">Verifying...</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Member Since</span>
                            <span class="text-gray-600">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Recent' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Fast Help & Links Card --}}
                <div class="bg-white border border-[#D4AF37]/30 p-6 rounded-xs shadow-xs space-y-4">
                    <h3 class="text-xs uppercase tracking-[0.2em] text-[#0E261C] font-bold border-b border-gray-100 pb-3">
                        Need Assistance?
                    </h3>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Our concierge desk is available 7 days a week for order inquiries, returns, and warranty requests.
                    </p>
                    <div class="space-y-2 pt-1 text-xs">
                        <a href="https://wa.me/918791227210" target="_blank" rel="noopener noreferrer"
                           class="flex items-center justify-between p-2.5 bg-gray-50 hover:bg-[#25D366]/10 hover:text-[#25D366] text-gray-700 transition-colors border border-gray-100 rounded-xs">
                            <span class="font-medium">WhatsApp Support</span>
                            <span class="font-mono font-semibold">+91 87912 27210</span>
                        </a>
                        <a href="mailto:vrazioofficial@gmail.com" 
                           class="flex items-center justify-between p-2.5 bg-gray-50 hover:bg-[#0E261C]/5 hover:text-[#0E261C] text-gray-700 transition-colors border border-gray-100 rounded-xs">
                            <span class="font-medium">Official Email</span>
                            <span class="text-gray-500">vrazioofficial@gmail.com</span>
                        </a>
                        <a href="{{ route('orders.index') }}" 
                           class="flex items-center justify-between p-2.5 bg-gray-50 hover:bg-[#0E261C]/5 hover:text-[#0E261C] text-gray-700 transition-colors border border-gray-100 rounded-xs">
                            <span class="font-medium">Find Any Order by Number</span>
                            <span class="text-[#D4AF37] font-semibold">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column: Orders & Warranty Section --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-[#D4AF37]/30 p-6 rounded-xs shadow-xs">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
                        <div>
                            <h2 class="text-sm uppercase tracking-[0.2em] text-[#0E261C] font-bold">
                                Your Recent Orders
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Orders placed with your account or phone number</p>
                        </div>
                        <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-[#D4AF37] hover:text-[#0E261C] uppercase tracking-wider">
                            Search All &rarr;
                        </a>
                    </div>

                    @if($orders->isEmpty())
                        <div class="p-8 text-center bg-gray-50 border border-dashed border-gray-200 rounded-xs space-y-3">
                            <div class="w-12 h-12 bg-white text-[#D4AF37] mx-auto flex items-center justify-center rounded-full shadow-xs border border-gray-100">
                                <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <h3 class="font-display text-base font-bold text-[#0E261C]">No Orders Found</h3>
                            <p class="text-xs text-gray-500 max-w-sm mx-auto">
                                You haven't placed an order under this account yet. Discover our signature jewelry and couture collections.
                            </p>
                            <div class="pt-2">
                                <a href="{{ route('products.index') }}" 
                                   class="inline-block px-5 py-2.5 bg-[#0E261C] text-[#D4AF37] text-xs uppercase font-semibold tracking-wider hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors rounded-xs shadow-xs">
                                    Explore Collections
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($orders as $order)
                                <div class="border border-gray-200 hover:border-[#D4AF37]/50 transition-colors p-4 sm:p-5 rounded-xs">
                                    {{-- Order Header --}}
                                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-gray-100">
                                        <div>
                                            <span class="font-mono font-bold text-sm text-[#0E261C]">{{ $order->order_number }}</span>
                                            <span class="text-xs text-gray-400 ml-2">{{ $order->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            @php
                                                $casualKey = $order->getCasualStatusKey();
                                                $badgeClasses = match($casualKey) {
                                                    'delivered' => 'bg-green-100 text-green-800 border-green-300',
                                                    'out_for_delivery' => 'bg-sky-100 text-sky-800 border-sky-300',
                                                    'shipped' => 'bg-blue-100 text-blue-800 border-blue-300',
                                                    'packed' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                    default => 'bg-gray-100 text-gray-800 border-gray-300'
                                                };
                                            @endphp
                                            <span class="px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider rounded-full border {{ $badgeClasses }}">
                                                {{ $order->getCasualStatusLabel() }}
                                            </span>

                                            @if($casualKey !== 'delivered' && $order->getEstimatedDeliveryText())
                                                <span class="px-2 py-0.5 text-[10px] font-medium text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-full">
                                                    Expected: {{ $order->getEstimatedDeliveryText() }}
                                                </span>
                                            @endif

                                            <span class="text-xs font-semibold font-mono text-gray-900 ml-1">
                                                ₹{{ number_format((float)$order->total_amount, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Order Items Preview --}}
                                    <div class="py-3 space-y-2">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D4AF37]"></span>
                                                    <span class="text-gray-800 font-medium">{{ $item->product_title }}</span>
                                                    <span class="text-gray-400">× {{ $item->quantity }}</span>
                                                </div>
                                                <span class="text-gray-600 font-mono">₹{{ number_format((float)$item->line_total, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Order Actions & Warranty Status --}}
                                    <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                                        <div class="text-xs text-gray-500">
                                            @if($order->warrantyClaim)
                                                @php
                                                    $wc = $order->warrantyClaim;
                                                    $wcStatusColor = match(strtolower($wc->status ?? 'submitted')) {
                                                        'approved', 'resolved' => 'text-green-700 bg-green-50 border-green-200',
                                                        'under review' => 'text-amber-700 bg-amber-50 border-amber-200',
                                                        'rejected' => 'text-red-700 bg-red-50 border-red-200',
                                                        default => 'text-blue-700 bg-blue-50 border-blue-200'
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-full border {{ $wcStatusColor }}">
                                                    Warranty Claimed ✔ ({{ ucfirst($wc->status) }})
                                                </span>
                                            @elseif($casualKey === 'delivered')
                                                <span class="text-emerald-700 text-[11px] font-medium">
                                                    Delivered • Eligible for Warranty Claim
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-[11px]">
                                                    Warranty claim available once order is delivered
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('order.invoice', $order->order_number) }}" target="_blank"
                                               class="px-3 py-1.5 text-xs text-gray-700 hover:text-[#0E261C] border border-[#D4AF37]/50 hover:border-[#D4AF37] hover:bg-[#D4AF37]/10 transition-colors rounded-xs flex items-center gap-1"
                                               title="View & Download PDF Invoice">
                                                <svg width="14" height="14" class="w-3.5 h-3.5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>Invoice</span>
                                            </a>

                                            <a href="{{ route('order.track', $order->order_number) }}" 
                                               class="px-3 py-1.5 text-xs text-gray-700 hover:text-[#0E261C] border border-gray-300 hover:border-[#0E261C] transition-colors rounded-xs">
                                                Track Delivery
                                            </a>

                                            @if($order->warrantyClaim)
                                                <a href="{{ route('warranty.show', $order->warrantyClaim->claim_number) }}" 
                                                   class="px-3 py-1.5 text-xs bg-[#0E261C] text-[#D4AF37] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors rounded-xs">
                                                    View Claim
                                                </a>
                                            @elseif($casualKey === 'delivered')
                                                <a href="{{ route('warranty.create', $order->order_number) }}" 
                                                   class="px-3 py-1.5 text-xs bg-[#0E261C] text-[#D4AF37] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors rounded-xs">
                                                    Claim Warranty
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Pagination --}}
                            <div class="mt-4">
                                {{ $orders->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- Edit Profile Modal --}}
    <div id="edit-profile-modal"
         x-data="{ 
            open: {{ ($errors->any() || request()->has('edit')) ? 'true' : 'false' }} || window.location.hash === '#edit'
         }"
         @open-edit-profile.window="open = true"
         @keydown.escape.window="open = false"
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[70] overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xs bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-[#D4AF37]/40 p-6 sm:p-8"
                 @click.away="open = false">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">Account Settings</span>
                        <h3 class="font-display text-xl font-bold text-[#0E261C] mt-0.5">Edit Patron Profile</h3>
                    </div>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 uppercase tracking-wider text-[10px]">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-xs focus:ring-1 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-xs">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 uppercase tracking-wider text-[10px]">Email Address</label>
                        <input type="email" value="{{ $user->email }}" disabled 
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-xs text-gray-500 text-xs cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">To change your primary email address, please contact concierge support.</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-1 uppercase tracking-wider text-[10px]">Mobile Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="10-digit mobile number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-xs focus:ring-1 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-xs font-mono">
                        <p class="text-[10px] text-amber-700 mt-1">Note: Updating your mobile number will reset SMS phone verification.</p>
                    </div>

                    <div class="pt-3 border-t border-gray-100">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-500 mb-2">Change Password (Optional)</span>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-gray-600 text-[10px] uppercase tracking-wider mb-0.5">Current Password</label>
                                <input type="password" name="current_password" placeholder="Leave blank to keep existing password"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-xs focus:ring-1 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-xs">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-gray-600 text-[10px] uppercase tracking-wider mb-0.5">New Password</label>
                                    <input type="password" name="new_password" placeholder="Min. 8 characters"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-xs focus:ring-1 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-xs">
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-[10px] uppercase tracking-wider mb-0.5">Confirm New Password</label>
                                    <input type="password" name="new_password_confirmation" placeholder="Confirm password"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-xs focus:ring-1 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-xs">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="open = false" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 text-xs font-semibold uppercase tracking-wider hover:bg-gray-50 rounded-xs transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 bg-[#0E261C] text-[#D4AF37] text-xs font-semibold uppercase tracking-wider hover:bg-[#D4AF37] hover:text-[#0E261C] rounded-xs transition-colors shadow-xs">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#edit' || window.location.search.includes('edit=')) {
            setTimeout(function() {
                window.dispatchEvent(new CustomEvent('open-edit-profile'));
            }, 100);
        }
    });
</script>
@endsection
