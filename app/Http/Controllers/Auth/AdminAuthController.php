<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    /**
     * Authenticate staff user into Filament admin panel via verified Google identity.
     */
    public function handleGoogleLogin(Request $request): JsonResponse
    {
        $idToken = $request->input('id_token');

        if (empty($idToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing Google authentication credential token.',
            ], 422);
        }

        $apiKey = config('services.firebase.api_key');
        $email = null;
        $firebaseUid = null;

        try {
            // Verify ID token with Google Identity Toolkit API
            $verifyRes = Http::withOptions(['verify' => true])
                ->timeout(10)
                ->post("https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}", [
                    'idToken' => $idToken,
                ]);

            if ($verifyRes->successful()) {
                $googleData = $verifyRes->json('users.0');
                $email = isset($googleData['email']) ? strtolower(trim((string) $googleData['email'])) : null;
                $firebaseUid = $googleData['localId'] ?? null;
            } else {
                // Fallback verification via Google tokeninfo
                $fallbackRes = Http::withOptions(['verify' => true])
                    ->timeout(10)
                    ->get("https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}");

                if ($fallbackRes->successful()) {
                    $email = strtolower(trim((string) $fallbackRes->json('email')));
                    $firebaseUid = $fallbackRes->json('sub');
                }
            }
        } catch (\Throwable $e) {
            Log::error('Admin Google Token Verification Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify Google security token. Please try again.',
            ], 500);
        }

        if (empty($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve verified email from Google identity token.',
            ], 401);
        }

        // Find matching staff member by email or google_id
        $user = User::where('email', $email)
            ->orWhere('google_id', $firebaseUid)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => "Access Denied: Google email '{$email}' is not an authorized administrator. Contact Super Administrator for access.",
            ], 403);
        }

        // Verify Staff Authority: Must be super_admin, admin, or sub_manager
        if (! in_array($user->role, ['super_admin', 'admin', 'sub_manager'], true)) {
            return response()->json([
                'success' => false,
                'message' => "Access Denied: Account '{$email}' does not possess administrative privileges.",
            ], 403);
        }

        // Verify Approval: Sub-Managers and Admins must be approved by Super Administrator
        if (! $user->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => "Access Restricted: Account '{$email}' is awaiting formal approval from the Super Administrator.",
            ], 403);
        }

        // Ensure google_id is updated and email verified
        if (empty($user->google_id) && $firebaseUid) {
            $user->update([
                'google_id' => $firebaseUid,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        }

        // Establish secure session
        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => "Welcome back, {$user->name}. Administrative clearance authenticated.",
            'redirect_url' => url('/admin'),
        ]);
    }
}
