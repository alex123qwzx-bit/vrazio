<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}
    /**
     * Show customer login and register portal.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account');
        }

        return view('auth.login');
    }

    /**
     * Handle customer login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // Check if input is an email or phone number
        $fieldType = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$fieldType => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('account'))
                ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle customer registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s\-()]{10,20}$/'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'An account with this email address already exists. Please log in instead.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'Your password must be at least 8 characters.',
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'role' => 'patron',
            'password' => Hash::make($validated['password']),
        ]);

        // Fire registered event to trigger verification email
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            // Mailer might be misconfigured in dev mode, don't crash registration
            report($e);
        }

        Auth::login($user);

        $intended = session()->pull('url.intended');
        if ($intended) {
            return redirect()->to($intended)
                ->with('success', 'Your account has been created successfully! Welcome, ' . $user->name . '.');
        }

        return redirect()->route('verification.notice')
            ->with('success', 'Your account has been created successfully!');
    }

    /**
     * Customer Account Dashboard.
     */
    public function dashboard(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $orders = Order::with(['items.product', 'warrantyClaim'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('client_phone', $user->phone)
                  ->orWhere('client_email', $user->email);
            })
            ->latest()
            ->paginate(5);

        return view('account.dashboard', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    /**
     * Show email verification notice page.
     */
    public function showVerifyEmail(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        return view('auth.verify-email');
    }

    /**
     * Handle email verification link callback.
     * Works seamlessly whether the patron is currently signed in or clicking from a new device/browser.
     */
    public function verifyEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::find($id);

        if (! $user) {
            return redirect()->route('login')->with('error', 'User account not found.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Invalid or expired verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        // Automatically log patron in and take them to their account
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('account')
            ->with('success', 'Your email address has been verified successfully! Welcome, ' . $user->name . '.');
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('success', 'A new verification link has been sent to your email address!');
        } catch (\Throwable $e) {
            \Log::error('Email verification error', ['error' => $e->getMessage()]);
            return back()->with('error', 'We made a mistake while sending your verification email. Please try again shortly or contact concierge support.');
        }
    }

    /**
     * Log the customer out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }

    /**
     * Send OTP for mobile login/registration via SMS.
     */
    public function sendLoginOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:10', 'max:20'],
        ]);

        $result = $this->otpService->sendOtp($validated['phone']);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Verify OTP and log patron in (or register new patron).
     */
    public function verifyLoginOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:10', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $cleanPhone = $this->otpService->normalizePhone($validated['phone']);
        $result = $this->otpService->verifyOtp($cleanPhone, $validated['otp']);

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        // Find existing user with this phone or create a new patron
        $user = User::where('phone', $cleanPhone)
            ->orWhere('phone', 'like', "%{$cleanPhone}")
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Patron ' . substr($cleanPhone, -4),
                'email' => $cleanPhone . '@vrazio.customer',
                'phone' => $cleanPhone,
                'role' => 'patron',
                'password' => Hash::make(Str::random(24)),
                'phone_verified_at' => now(),
            ]);
        } else {
            $user->update(['phone_verified_at' => now()]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Signed in successfully! Welcome, ' . $user->name,
            'redirect' => session()->pull('url.intended', route('account')),
        ]);
    }

    /**
     * Send or verify SMS OTP for an already logged-in customer in their dashboard.
     */
    public function verifyDashboardPhone(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $action = $request->input('action', 'send');

        if ($action === 'send') {
            $phone = $request->input('phone', $user->phone);
            if (! $phone) {
                return response()->json(['success' => false, 'message' => 'Please enter a valid mobile number.'], 422);
            }

            $cleanPhone = $this->otpService->normalizePhone($phone);
            $user->update(['phone' => $cleanPhone]);

            $result = $this->otpService->sendOtp($cleanPhone);
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($action === 'verify') {
            $otp = (string) $request->input('otp', '');
            $cleanPhone = $this->otpService->normalizePhone($user->phone ?? '');

            $result = $this->otpService->verifyOtp($cleanPhone, $otp);
            if ($result['success']) {
                $user->update(['phone_verified_at' => now()]);
                return response()->json([
                    'success' => true,
                    'message' => 'Mobile number verified successfully! ✔',
                ]);
            }

            return response()->json($result, 422);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    /**
     * Authenticate or register patron via verified Firebase token (Phone SMS or Google).
     */
    public function handleFirebaseSession(Request $request): JsonResponse
    {
        $idToken = (string) $request->input('idToken', '');
        $provider = (string) $request->input('provider', 'phone');

        if (empty($idToken)) {
            return response()->json(['success' => false, 'message' => 'Missing Firebase authentication token.'], 422);
        }

        $apiKey = config('services.firebase.api_key');

        // Verify ID token with Google Identity Toolkit API (with SSL verification enabled)
        try {
            $verifyRes = Http::timeout(10)
                ->post("https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}", [
                    'idToken' => $idToken,
                ]);

            if (! $verifyRes->successful() || empty($verifyRes->json('users.0'))) {
                return response()->json(['success' => false, 'message' => 'Invalid or expired Firebase session.'], 401);
            }

            $googleData = $verifyRes->json('users.0');
        } catch (\Throwable $e) {
            \Log::error('Firebase Token Verify Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Authentication verification error.'], 500);
        }

        $firebaseUid = $googleData['localId'] ?? null;
        $phoneNumber = $googleData['phoneNumber'] ?? null;
        $email = isset($googleData['email']) ? strtolower(trim((string) $googleData['email'])) : null;
        $displayName = $googleData['displayName'] ?? null;
        $photoUrl = $googleData['photoUrl'] ?? null;

        $user = null;

        try {
            // 1. Phone SMS Auth — Strictly read phone from verified Firebase token, NEVER from $request->input('phone')
            if ($provider === 'phone') {
                if (empty($phoneNumber)) {
                    return response()->json(['success' => false, 'message' => 'Token does not contain a verified mobile number.'], 403);
                }

                $cleanPhone = $this->otpService->normalizePhone($phoneNumber);
                if (empty($cleanPhone)) {
                    return response()->json(['success' => false, 'message' => 'Invalid phone number format in token.'], 403);
                }

                $user = User::where('phone', $cleanPhone)
                    ->orWhere('phone', 'like', "%{$cleanPhone}")
                    ->first();

                if (! $user) {
                    $user = User::create([
                        'name' => $displayName ?? ('Patron ' . substr($cleanPhone, -4)),
                        'email' => $email ?? ($cleanPhone . '@vrazio.customer'),
                        'phone' => $cleanPhone,
                        'role' => 'patron',
                        'password' => Hash::make(Str::random(32)),
                        'phone_verified_at' => now(),
                    ]);
                } else {
                    $user->update(['phone_verified_at' => now()]);
                }
            }

            // 2. Google Sign-In — Authenticate strictly via verified Google email and UID
            if ($provider === 'google' || (! $user && !empty($email))) {
                $user = User::where('google_id', $firebaseUid)
                    ->orWhere('email', $email)
                    ->first();

                if (! $user) {
                    $user = User::create([
                        'name' => $displayName ?? 'Patron',
                        'email' => $email,
                        'google_id' => $firebaseUid,
                        'avatar' => $photoUrl,
                        'role' => 'patron',
                        'password' => Hash::make(Str::random(32)),
                        'email_verified_at' => now(),
                    ]);
                } else {
                    $user->update([
                        'google_id' => $firebaseUid,
                        'avatar' => $photoUrl ?? $user->avatar,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Firebase Authentication Session Error', [
                'error' => $e->getMessage(),
                'provider' => $provider,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'We made a mistake while setting up your session. Please try again or sign in with password.',
            ], 500);
        }

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unable to establish customer account.'], 422);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Welcome, ' . $user->name . '!',
            'redirect' => session()->pull('url.intended', route('account')),
        ]);
    }

    /**
     * Update patron profile information.
     */
    public function updateProfile(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\s\-()]{10,20}$/'],
            'date_of_birth' => ['nullable', 'date'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Please enter your full name.',
            'phone.regex' => 'Please enter a valid mobile number.',
            'current_password.required_with' => 'Current password is required to change your password.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.min' => 'The new password must be at least 8 characters.',
        ]);

        // If password update requested, verify current password
        if (! empty($validated['new_password'])) {
            if (! empty($user->password) && ! Hash::check($validated['current_password'] ?? '', $user->password)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'The current password provided is incorrect.'], 422);
                }
                return back()->withErrors(['current_password' => 'The current password provided is incorrect.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = trim($validated['name']);
        if (array_key_exists('date_of_birth', $validated)) {
            $user->date_of_birth = $validated['date_of_birth'] ? $validated['date_of_birth'] : null;
        }

        // Handle phone update
        if (array_key_exists('phone', $validated)) {
            if (! empty($validated['phone'])) {
                $cleanPhone = $this->otpService->normalizePhone($validated['phone']);
                
                // Check if phone changed
                if ($cleanPhone !== $user->phone) {
                    // Check if already taken by another account
                    $existing = User::where('phone', $cleanPhone)->where('id', '!=', $user->id)->first();
                    if ($existing) {
                        if ($request->expectsJson()) {
                            return response()->json(['success' => false, 'message' => 'This mobile number is already registered with another patron account.'], 422);
                        }
                        return back()->withErrors(['phone' => 'This mobile number is already registered with another patron account.']);
                    }

                    $user->phone = $cleanPhone;
                    $user->phone_verified_at = null; // Require re-verification
                }
            } else {
                $user->phone = null;
                $user->phone_verified_at = null;
            }
        }

        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your profile details have been successfully updated.',
            ]);
        }

        return redirect()->route('account')->with('success', 'Your profile details have been successfully updated.');
    }
}
