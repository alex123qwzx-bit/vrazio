@extends('layouts.app')

@section('title', 'Sign In or Create Account | VRAZIO')
@section('meta_description', 'Sign in to your VRAZIO account via instant Google Sign-In or Mobile SMS OTP.')

@section('content')
{{-- Firebase App & Auth SDK --}}
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>

<div class="bg-[#FAF8F5] min-h-screen py-12 md:py-16" 
     x-data="{ 
        tab: '{{ $errors->has('name') || $errors->has('password_confirmation') ? 'register' : 'login' }}',
        loginMethod: 'otp',
        phone: '{{ old('phone') }}',
        otp: '',
        otpSent: false,
        timer: 0,
        interval: null,
        loading: false,
        googleLoading: false,
        errorMessage: '',
        successMessage: '',
        devOtp: '',

        init() {
            // Initialize Firebase SDK with user project credentials
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

        initRecaptcha() {
            if (!window.recaptchaVerifier) {
                window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                    'size': 'invisible',
                    'callback': () => {},
                    'expired-callback': () => {
                        this.errorMessage = 'reCAPTCHA expired. Please try sending OTP again.';
                    }
                });
            }
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
                    if (error.code === 'auth/unauthorized-domain') {
                        this.errorMessage = 'This domain is not authorized in Firebase Console. Please browse to http://localhost:8000/login or add 127.0.0.1 to Firebase Console > Authentication > Settings > Authorized domains.';
                    } else if (error.message && error.message.includes('missing initial state')) {
                        this.errorMessage = 'Google Sign-In blocked by browser cookie settings or unauthorized domain. Please access the store via http://localhost:8000/login.';
                    } else {
                        this.errorMessage = error.message || 'Unable to sign in with Google. Please try again.';
                    }
                });
        },

        // Send Mobile SMS OTP via Firebase
        sendOtp() {
            let clean = (this.phone || '').replace(/\D/g, '');
            if (clean.length < 10) {
                this.errorMessage = 'Please enter a valid 10-digit mobile number.';
                return;
            }

            clean = clean.slice(-10);
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                this.initRecaptcha();
                const fullPhone = '+91' + clean;

                firebase.auth().signInWithPhoneNumber(fullPhone, window.recaptchaVerifier)
                    .then((confirmationResult) => {
                        window.confirmationResult = confirmationResult;
                        this.loading = false;
                        this.otpSent = true;
                        this.successMessage = 'SMS with 6-digit OTP has been sent to ' + fullPhone + ' via Google Firebase!';
                        this.startTimer(60);
                    })
                    .catch((error) => {
                        this.loading = false;
                        if (window.recaptchaVerifier) {
                            try { window.recaptchaVerifier.render().then(widgetId => grecaptcha.reset(widgetId)); } catch(e) {}
                        }
                        if (error.code === 'auth/billing-not-enabled') {
                            this.errorMessage = 'Google Firebase requires a linked billing account (Blaze plan) to send real live SMS to prevent spam. However, you can add your mobile as a Test Number in Firebase Console (Authentication > Sign-in method > Phone > Phone numbers for testing) to test completely FREE with no card or billing needed!';
                        } else if (error.code === 'auth/operation-not-allowed' || (error.message && error.message.includes('region'))) {
                            this.errorMessage = 'SMS sending to India (+91) needs to be enabled in Firebase Console (Authentication > Settings > SMS Region Policy), OR you can add your phone as a Test Number in Firebase Console (Authentication > Sign-in method > Phone > Phone numbers for testing) to test immediately without quota limits.';
                        } else if (error.code === 'auth/too-many-requests') {
                            this.errorMessage = 'Daily SMS quota reached for today (10 SMS/day on free tier). You can add test phone numbers in Firebase Console > Phone to test unlimited times for free!';
                        } else {
                            this.errorMessage = error.message || 'Failed to send SMS OTP. Please check the number and try again.';
                        }
                    });
            } catch (err) {
                this.loading = false;
                this.errorMessage = err.message || 'Error initializing verification.';
            }
        },

        // Verify OTP via Firebase and authenticate in Laravel
        verifyOtp() {
            if (!this.otp || this.otp.length !== 6) {
                this.errorMessage = 'Please enter the 6-digit OTP received via SMS.';
                return;
            }

            if (!window.confirmationResult) {
                this.errorMessage = 'Session expired. Please request a new OTP.';
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            window.confirmationResult.confirm(this.otp)
                .then((result) => {
                    return result.user.getIdToken();
                })
                .then((idToken) => {
                    let clean = (this.phone || '').replace(/\D/g, '').slice(-10);
                    return fetch('{{ route('auth.firebase.session') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            idToken: idToken, 
                            provider: 'phone',
                            phone: clean 
                        })
                    });
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        window.location.href = data.redirect || '{{ route('account') }}';
                    } else {
                        this.errorMessage = data.message || 'Verification failed.';
                    }
                })
                .catch((error) => {
                    this.loading = false;
                    this.errorMessage = error.message || 'Invalid OTP code. Please check and try again.';
                });
        },

        startTimer(seconds) {
            this.timer = seconds;
            if (this.interval) clearInterval(this.interval);
            this.interval = setInterval(() => {
                if (this.timer > 0) {
                    this.timer--;
                } else {
                    clearInterval(this.interval);
                }
            }, 1000);
        }
     }">

    {{-- Invisible reCAPTCHA container for Firebase Phone Auth --}}
    <div id="recaptcha-container"></div>

    <div class="max-w-md mx-auto px-6">
        <x-breadcrumbs :links="['Account' => '']" />

        <div class="text-center my-8">
            <span class="text-xs uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">Welcome</span>
            <h1 class="font-display text-3xl font-bold text-[#0E261C] mt-1" x-text="tab === 'login' ? 'Sign In to Your Account' : 'Create an Account'">
                Sign In to Your Account
            </h1>
            <p class="text-xs text-gray-500 mt-2">
                Access your orders, track shipments, and claim warranty support.
            </p>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 text-green-800 text-xs sm:text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 text-red-800 text-xs sm:text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tab Switcher --}}
        <div class="flex border-b border-gray-200 mb-6 bg-white rounded-t-lg overflow-hidden border border-[#D4AF37]/20 border-b-0">
            <button type="button" 
                    @click="tab = 'login'" 
                    :class="tab === 'login' ? 'bg-[#0E261C] text-[#D4AF37] font-bold' : 'text-gray-500 hover:text-[#0E261C] bg-gray-50'"
                    class="flex-1 py-3.5 text-xs uppercase tracking-wider text-center transition-all">
                Sign In
            </button>
            <button type="button" 
                    @click="tab = 'register'" 
                    :class="tab === 'register' ? 'bg-[#0E261C] text-[#D4AF37] font-bold' : 'text-gray-500 hover:text-[#0E261C] bg-gray-50'"
                    class="flex-1 py-3.5 text-xs uppercase tracking-wider text-center transition-all">
                Create Account
            </button>
        </div>

        {{-- Form Container --}}
        <div class="bg-white border border-[#D4AF37]/20 p-8 shadow-sm rounded-b-lg">
            {{-- Dynamic JS Messages --}}
            <div x-show="errorMessage" x-text="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xs" x-cloak></div>
            <div x-show="successMessage" x-text="successMessage" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xs" x-cloak></div>

            {{-- 1. Google One-Click Sign-In via Firebase --}}
            <div class="mb-6">
                <button type="button"
                        @click="signInWithGoogle()"
                        :disabled="googleLoading"
                        class="w-full flex items-center justify-center gap-3 py-3 px-4 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xs shadow-xs transition-all hover:border-[#D4AF37] group disabled:opacity-50">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span class="group-hover:text-[#0E261C]" x-show="!googleLoading" x-text="tab === 'login' ? 'Continue with Google' : 'Sign Up with Google'">
                        Continue with Google
                    </span>
                    <span x-show="googleLoading">Connecting to Google...</span>
                </button>
                
                <div class="relative my-6 text-center">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-[10px] uppercase tracking-widest">
                        <span class="bg-white px-3 text-gray-400 font-medium">Or continue with mobile SMS</span>
                    </div>
                </div>
            </div>

            {{-- 2. LOGIN: METHOD CHOOSER --}}
            <div x-show="tab === 'login'">
                <div class="flex items-center justify-center p-1 bg-gray-100 rounded-xs mb-6 text-xs">
                    <button type="button" 
                            @click="loginMethod = 'otp'"
                            :class="loginMethod === 'otp' ? 'bg-white text-[#0E261C] font-bold shadow-xs' : 'text-gray-500 hover:text-gray-900'"
                            class="flex-1 py-2 rounded-xs transition-all text-center flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Mobile SMS OTP</span>
                    </button>
                    <button type="button" 
                            @click="loginMethod = 'password'"
                            :class="loginMethod === 'password' ? 'bg-white text-[#0E261C] font-bold shadow-xs' : 'text-gray-500 hover:text-gray-900'"
                            class="flex-1 py-2 rounded-xs transition-all text-center flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Password</span>
                    </button>
                </div>

                {{-- 2A: MOBILE OTP LOGIN --}}
                <div x-show="loginMethod === 'otp'" class="space-y-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold mb-1">
                            Mobile Phone Number <span class="text-red-500">*</span>
                        </label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 text-xs text-gray-500 bg-gray-50 border border-r-0 border-gray-300 font-mono">
                                +91
                            </span>
                            <input type="tel" 
                                   x-model="phone" 
                                   :disabled="otpSent"
                                   placeholder="Enter 10-digit mobile number"
                                   maxlength="10"
                                   class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-r-xs font-mono">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Google will send a real 6-digit OTP code directly to your mobile via SMS.</p>
                    </div>

                    {{-- OTP Input (Visible after OTP is sent) --}}
                    <div x-show="otpSent" x-cloak class="space-y-2 pt-2 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold">
                                Enter 6-Digit SMS Code <span class="text-red-500">*</span>
                            </label>
                            <button type="button" 
                                    @click="otpSent = false; otp = ''" 
                                    class="text-[11px] text-gray-500 hover:text-[#0E261C] underline">
                                Change number
                            </button>
                        </div>
                        <input type="text" 
                               x-model="otp" 
                               placeholder="123456" 
                               maxlength="6"
                               class="w-full text-center tracking-[0.5em] text-lg font-bold py-2.5 border-2 border-[#D4AF37] focus:outline-none rounded-xs font-mono">
                        
                        <div class="flex items-center justify-between text-xs pt-1">
                            <span class="text-gray-500 text-[11px]">Didn't get code?</span>
                            <button type="button" 
                                    @click="sendOtp()" 
                                    :disabled="timer > 0 || loading"
                                    class="text-xs font-semibold text-[#0E261C] hover:text-[#D4AF37] disabled:text-gray-400">
                                <span x-show="timer > 0" x-text="'Resend in ' + timer + 's'"></span>
                                <span x-show="timer === 0">Resend SMS Code</span>
                            </button>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="pt-2">
                        <template x-if="!otpSent">
                            <button type="button" 
                                    @click="sendOtp()" 
                                    :disabled="loading"
                                    class="w-full py-3.5 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all shadow-md disabled:opacity-50">
                                <span x-show="!loading">Send SMS OTP</span>
                                <span x-show="loading">Sending Real SMS via Google...</span>
                            </button>
                        </template>

                        <template x-if="otpSent">
                            <button type="button" 
                                    @click="verifyOtp()" 
                                    :disabled="loading"
                                    class="w-full py-3.5 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all shadow-md disabled:opacity-50">
                                <span x-show="!loading">Verify & Sign In</span>
                                <span x-show="loading">Verifying Code...</span>
                            </button>
                        </template>
                    </div>

                    <div class="text-center pt-4 border-t border-gray-100 text-xs text-gray-500">
                        Don't have an account yet? 
                        <button type="button" @click="tab = 'register'" class="text-[#0E261C] font-semibold underline hover:text-[#D4AF37]">
                            Create an account
                        </button>
                    </div>
                </div>

                {{-- 2B: PASSWORD LOGIN --}}
                <form action="{{ route('login.post') }}" method="POST" x-show="loginMethod === 'password'" class="space-y-4" x-cloak>
                    @csrf
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold mb-1">
                            Email Address or Phone <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="email" value="{{ old('email') }}" required
                               placeholder="Enter your email or phone number"
                               class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold">
                                Password <span class="text-red-500">*</span>
                            </label>
                        </div>
                        <input type="password" name="password" required
                               placeholder="Enter your password"
                               class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs">
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer text-gray-600">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#0E261C] focus:ring-[#D4AF37]">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <div class="pt-3">
                        <button type="submit" 
                                class="w-full py-3.5 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all shadow-md">
                            Sign In with Password
                        </button>
                    </div>

                    <div class="text-center pt-4 border-t border-gray-100 text-xs text-gray-500">
                        Don't have an account yet? 
                        <button type="button" @click="tab = 'register'" class="text-[#0E261C] font-semibold underline hover:text-[#D4AF37]">
                            Create an account
                        </button>
                    </div>
                </form>
            </div>

            {{-- 3. REGISTER FORM --}}
            <form action="{{ route('register.post') }}" method="POST" x-show="tab === 'register'" class="space-y-4" x-cloak>
                @csrf
                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Your full name"
                           class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold mb-1">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="yourname@gmail.com"
                           class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold mb-1">
                        Mobile Phone Number <span class="text-red-500">*</span>
                    </label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 text-xs text-gray-500 bg-gray-50 border border-r-0 border-gray-300 font-mono">
                            +91
                        </span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                               placeholder="10-digit mobile number"
                               class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-r-xs font-mono">
                    </div>
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold mb-1">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required
                           placeholder="At least 8 characters"
                           class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs">
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-700 font-semibold mb-1">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required
                           placeholder="Re-type your password"
                           class="w-full text-xs px-3.5 py-2.5 border border-gray-300 focus:border-[#D4AF37] focus:outline-none rounded-xs">
                </div>

                <div class="pt-3">
                    <button type="submit" 
                            class="w-full py-3.5 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#D4AF37] hover:text-[#0E261C] transition-all shadow-md">
                        Create Account
                    </button>
                </div>

                <div class="text-center pt-4 border-t border-gray-100 text-xs text-gray-500">
                    Already have an account? 
                    <button type="button" @click="tab = 'login'" class="text-[#0E261C] font-semibold underline hover:text-[#D4AF37]">
                        Sign in here
                    </button>
                </div>
            </form>
        </div>

        {{-- WhatsApp Help --}}
        <div class="mt-8 text-center text-xs text-gray-500">
            Need help with your account? 
            <a href="https://wa.me/918979622310?text=Hi%20VRAZIO%2C%20I%20need%20help%20with%20my%20account" target="_blank" class="text-emerald-700 font-semibold hover:underline">
                Chat with us on WhatsApp (+91 89796 22310)
            </a>
        </div>
    </div>
</div>
@endsection
