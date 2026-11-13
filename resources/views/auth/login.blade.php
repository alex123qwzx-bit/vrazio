@extends('layouts.app')

@section('title', 'Sign In with Google | VRAZIO')
@section('meta_description', 'Sign in to your VRAZIO account via instant Google Sign-In (Gmail).')

@section('content')
{{-- Firebase App & Auth SDK --}}
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>
{{-- Google Identity Services SDK (Native One Tap & Direct Token Fallback) --}}
<script src="https://accounts.google.com/gsi/client" async defer></script>

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

            if (typeof firebase !== 'undefined' && !firebase.apps.length) {
                try {
                    firebase.initializeApp(firebaseConfig);
                } catch(e) {
                    console.warn('Firebase init:', e);
                }
            }

            // Resilient persistence: try LOCAL -> SESSION -> NONE
            if (typeof firebase !== 'undefined' && firebase.auth) {
                firebase.auth().setPersistence(firebase.auth.Auth.Persistence.LOCAL)
                    .catch(() => firebase.auth().setPersistence(firebase.auth.Auth.Persistence.SESSION))
                    .catch(() => firebase.auth().setPersistence(firebase.auth.Auth.Persistence.NONE))
                    .catch(() => {});

                // Seamlessly resolve redirect result if returning from a redirect sign-in
                firebase.auth().getRedirectResult()
                    .then((result) => {
                        if (result && result.user) {
                            this.googleLoading = true;
                            this.errorMessage = '';
                            this.successMessage = 'Authenticating patron credentials...';
                            return result.user.getIdToken()
                                .then(idToken => this.verifySession(idToken, 'google'));
                        }
                    })
                    .catch((err) => {
                        this.handleAuthError(err);
                    });
            }

            // Initialize Google Identity Services (GSI)
            this.initGoogleIdentityServices();
        },

        initGoogleIdentityServices() {
            const googleClientId = '{{ config('services.firebase.google_client_id') }}';
            if (!googleClientId) return;

            const setupGsi = () => {
                if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
                    try {
                        google.accounts.id.initialize({
                            client_id: googleClientId,
                            callback: (response) => {
                                if (response && response.credential) {
                                    this.googleLoading = true;
                                    this.errorMessage = '';
                                    this.successMessage = 'Authenticating Google credentials...';
                                    this.verifySession(response.credential, 'google');
                                }
                            },
                            auto_select: false,
                            cancel_on_tap_outside: true,
                        });
                        google.accounts.id.prompt();
                    } catch (e) {
                        console.warn('Google Identity Services notice:', e);
                    }
                }
            };

            if (typeof google !== 'undefined' && google.accounts) {
                setupGsi();
            } else {
                window.addEventListener('load', setupGsi);
            }
        },

        verifySession(idToken, provider = 'google') {
            this.googleLoading = true;
            return fetch('{{ route('auth.firebase.session') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ idToken: idToken, provider: provider })
            })
            .then(res => res.json())
            .then(data => {
                this.googleLoading = false;
                if (data.success) {
                    this.successMessage = 'Welcome to VRAZIO. Redirecting to your patron suite...';
                    window.location.href = data.redirect || '{{ route('account') }}';
                } else {
                    this.errorMessage = data.message || 'Authentication could not be established.';
                }
            })
            .catch(err => {
                this.googleLoading = false;
                this.errorMessage = 'Network connection interrupted during verification. Please try again.';
            });
        },

        // Google One-Click Authentication via Firebase with automatic redirect fallback
        signInWithGoogle() {
            this.googleLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            const fallbackToRedirect = () => {
                if (typeof firebase !== 'undefined' && firebase.auth) {
                    try {
                        const provider = new firebase.auth.GoogleAuthProvider();
                        provider.setCustomParameters({ prompt: 'select_account' });
                        this.successMessage = 'Opening secure Google authentication...';
                        firebase.auth().signInWithRedirect(provider);
                        return;
                    } catch (e) {
                        this.handleAuthError(e);
                        return;
                    }
                }

                if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
                    google.accounts.id.prompt();
                    this.googleLoading = false;
                    return;
                }

                this.googleLoading = false;
                this.errorMessage = 'Google authentication is initializing. Please refresh and try again.';
            };

            if (typeof firebase === 'undefined' || !firebase.auth) {
                fallbackToRedirect();
                return;
            }

            const provider = new firebase.auth.GoogleAuthProvider();
            provider.setCustomParameters({ prompt: 'select_account' });

            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    return result.user.getIdToken();
                })
                .then((idToken) => {
                    return this.verifySession(idToken, 'google');
                })
                .catch((error) => {
                    // 1. User closed popup window
                    if (error.code === 'auth/popup-closed-by-user' || error.code === 'auth/cancelled-popup-request') {
                        this.googleLoading = false;
                        return;
                    }

                    // 2. Storage / iframe / cross-origin / internal error:
                    // Automatically fallback to redirect flow without displaying any raw technical error!
                    const isInternalOrPopupError =
                        error.code === 'auth/internal-error' ||
                        error.code === 'auth/popup-blocked' ||
                        error.code === 'auth/web-storage-unsupported' ||
                        (error.message && (
                            error.message.includes('internal-error') ||
                            error.message.includes('popup') ||
                            error.message.includes('storage') ||
                            error.message.includes('cookie') ||
                            error.message.includes('partition')
                        ));

                    if (isInternalOrPopupError) {
                        console.warn('Popup authentication restricted by browser environment. Initiating seamless redirect flow...');
                        fallbackToRedirect();
                        return;
                    }

                    this.googleLoading = false;
                    this.handleAuthError(error);
                });
        },

        handleAuthError(error) {
            this.googleLoading = false;
            if (!error) return;

            const code = error.code || '';
            const msg = (error.message || '').toLowerCase();

            if (code === 'auth/popup-closed-by-user' || code === 'auth/cancelled-popup-request') {
                return;
            }

            if (code === 'auth/unauthorized-domain' || msg.includes('unauthorized-domain') || msg.includes('invalid-continue-uri')) {
                const currentHost = window.location.hostname;
                this.errorMessage = 'Google Sign-In configuration: Domain "' + currentHost + '" must be authorized in Firebase Console.';
                return;
            }

            if (code === 'auth/network-request-failed' || msg.includes('network-request-failed')) {
                this.errorMessage = 'Network connection issue. Please check your internet connection and try again.';
                return;
            }

            // GUARANTEE: Never leak raw "Firebase: Error" or technical stack traces to the user
            this.errorMessage = 'Unable to complete Google Sign-In at this moment. Please try again or sign in with your staff credentials.';
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
