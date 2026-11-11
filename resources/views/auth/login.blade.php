@extends('layouts.app')

@section('title', 'Sign In with Google | VRAZIO')
@section('meta_description', 'Sign in to your VRAZIO account via instant Google Sign-In (Gmail).')

@section('content')
{{-- Firebase App & Auth SDK --}}
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('customerAuth', () => ({
        googleLoading: false,
        errorMessage: '',
        successMessage: '',

        init() {
            const firebaseConfig = {
                apiKey: '{{ config('services.firebase.api_key') }}',
                authDomain: '{{ config('services.firebase.auth_domain') }}',
                projectId: '{{ config('services.firebase.project_id') }}',
                storageBucket: '{{ config('services.firebase.storage_bucket') }}',
                messagingSenderId: '{{ config('services.firebase.messaging_sender_id') }}',
                appId: '{{ config('services.firebase.app_id') }}'
            };

            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }
            try {
                firebase.auth().setPersistence(firebase.auth.Auth.Persistence.LOCAL);
            } catch(e) {}
        },

        // Google One-Click Authentication via Firebase
        signInWithGoogle() {
            this.googleLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            const provider = new firebase.auth.GoogleAuthProvider();
            provider.setCustomParameters({ prompt: 'select_account' });

            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    return result.user.getIdToken();
                })
                .then((idToken) => {
                    return fetch('{{ route('auth.firebase.session') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ idToken: idToken, provider: 'google' })
                    });
                })
                .then(res => res.json())
                .then(data => {
                    this.googleLoading = false;
                    if (data.success) {
                        window.location.href = data.redirect || '{{ route('account') }}';
                    } else {
                        this.errorMessage = data.message || 'Google authentication failed.';
                    }
                })
                .catch((error) => {
                    this.googleLoading = false;
                    if (error.code === 'auth/popup-closed-by-user') {
                        return;
                    }
                    const isDomainAuthError = error.code === 'auth/unauthorized-domain'
                        || error.code === 'auth/invalid-continue-uri'
                        || (error.message && (
                            error.message.includes('unauthorized-domain')
                            || error.message.includes('invalid-continue-uri')
                            || error.message.includes('missing initial state')
                        ));

                    if (isDomainAuthError) {
                        const currentHost = window.location.hostname;
                        this.errorMessage = 'Google Sign-In requires "' + currentHost + '" in Firebase Authorized Domains.';
                    } else {
                        this.errorMessage = error.message || 'Unable to sign in with Google. Please try again.';
                    }
                });
        }
    }));
});
</script>

<div class="bg-[#FAF8F5] min-h-[80vh] flex items-center justify-center py-12 md:py-16" x-data="customerAuth">
    <div class="max-w-md w-full mx-auto px-6">
        <x-breadcrumbs :links="['Account' => '']" />

        <div class="text-center my-6">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">Welcome to VRAZIO</span>
            <h1 class="font-display text-3xl font-bold text-[#0E261C] mt-1">
                Patron Sign In
            </h1>
            <p class="text-xs text-gray-500 mt-2">
                Sign in to manage your orders, track shipments, and request warranty services.
            </p>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 text-green-800 text-xs sm:text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 bg-amber-50 border-l-4 border-[#D4AF37] text-amber-900 text-xs sm:text-sm">
                {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 text-red-800 text-xs sm:text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Main Google Sign In Card --}}
        <div class="bg-white border border-[#D4AF37]/30 p-8 sm:p-10 shadow-sm rounded-xs text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-[#0E261C] border border-[#D4AF37] flex items-center justify-center text-[#D4AF37] shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>

            <span class="text-[10px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">Gmail Authentication</span>
            <h2 class="font-display text-xl font-bold text-[#0E261C] mt-1 mb-2">Continue with Google</h2>
            <p class="text-xs text-gray-500 mb-6 max-w-sm mx-auto leading-relaxed">
                One-tap instant access for all patrons. No passwords or SMS OTP codes required.
            </p>

            {{-- Dynamic JS Messages --}}
            <div x-show="errorMessage" x-text="errorMessage" class="mb-5 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xs text-left" x-cloak></div>
            <div x-show="successMessage" x-text="successMessage" class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xs text-left" x-cloak></div>

            <button type="button"
                    @click="signInWithGoogle()"
                    :disabled="googleLoading"
                    class="w-full flex items-center justify-center gap-3 py-3.5 px-6 border-2 border-[#D4AF37] bg-white hover:bg-[#FAF8F5] text-[#0E261C] text-xs uppercase tracking-wider font-bold rounded-xs shadow-sm transition-all hover:shadow-md disabled:opacity-50 cursor-pointer">
                <svg width="20" height="20" class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span x-show="!googleLoading">Sign In with Google (Gmail)</span>
                <span x-show="googleLoading">Authenticating with Google...</span>
            </button>

            <div class="mt-6 flex items-center justify-center gap-2 text-[11px] text-gray-500">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Instant 1-Click Access &bull; Verified Google Security</span>
            </div>

            {{-- Concierge / Staff / Admin Login Toggle --}}
            <div x-data="{ showAdmin: false }" class="mt-8 pt-6 border-t border-gray-100">
                <button type="button" @click="showAdmin = !showAdmin" class="text-[10px] uppercase tracking-wider text-gray-400 hover:text-gray-600 transition-colors">
                    Store Concierge &amp; Staff Access &darr;
                </button>
                <div x-show="showAdmin" x-cloak class="mt-4 text-left p-4 bg-gray-50 border border-gray-200 rounded-xs space-y-3">
                    <form action="{{ route('login.post') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-gray-600 font-semibold mb-1">Staff Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@vrazio.com" class="w-full text-xs px-3 py-2 border border-gray-300 rounded-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-gray-600 font-semibold mb-1">Password</label>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full text-xs px-3 py-2 border border-gray-300 rounded-xs">
                        </div>
                        <button type="submit" class="w-full py-2 bg-[#0E261C] text-[#D4AF37] text-xs font-semibold uppercase tracking-wider hover:bg-[#D4AF37] hover:text-[#0E261C] transition-colors rounded-xs">
                            Sign In with Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-gray-400">
            Need assistance with your account? 
            <a href="https://wa.me/918791227210?text=Hello%20VRAZIO%20Concierge,%20I%20need%20help%20signing%20in" target="_blank" class="text-[#0E261C] font-semibold underline hover:text-[#D4AF37]">
                WhatsApp Concierge
            </a>
        </div>
    </div>
</div>
@endsection
