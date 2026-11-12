<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Services\RazorpayService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PriveClubController extends Controller
{
    /**
     * Display VRAZIO PRIVÉ CLUB overview, tier plans, benefits matrix, and FAQs.
     */
    public function index(): View
    {
        $plans = MembershipPlan::active()->ordered()->get();
        $userMembership = auth()->check() ? auth()->user()->activeMembership() : null;

        return view('prive-club.index', [
            'plans' => $plans,
            'userMembership' => $userMembership,
        ]);
    }

    /**
     * Public Digital Membership Card Verification endpoint (accessed via QR code).
     */
    public function verify(string $memberId): View
    {
        $cleanId = strtoupper(trim($memberId));
        $membership = Membership::with('user', 'plan')
            ->where('member_id', $cleanId)
            ->first();

        return view('prive-club.verify', [
            'memberId' => $cleanId,
            'membership' => $membership,
            'isValid' => $membership ? $membership->isActive() : false,
        ]);
    }

    /**
     * Initiate Membership enrollment / purchase checkout.
     */
    public function checkout(string $slug): View|RedirectResponse
    {
        if (! auth()->check()) {
            session(['url.intended' => route('prive-club.checkout', $slug)]);
            return redirect()->route('login')->with('info', 'Please sign in or create an account to join VRAZIO PRIVÉ CLUB.');
        }

        $user = auth()->user();
        if ($user->isPriveMember()) {
            return redirect()->route('account')->with('info', 'You already have an active VRAZIO PRIVÉ CLUB membership.');
        }

        $plan = MembershipPlan::where('slug', $slug)->active()->firstOrFail();

        // Create Razorpay Order if key is available
        $razorpayOrderId = null;
        $razorpayKey = (string) (config('services.razorpay.key') ?: env('RAZORPAY_KEY'));
        $razorpayError = null;

        if (!empty($razorpayKey) && !str_contains($razorpayKey, 'xxxx')) {
            try {
                $razorpay = app(RazorpayService::class);
                $receipt = 'VPC-' . strtoupper($plan->slug) . '-' . time();
                $rpOrder = $razorpay->createOrder(
                    receipt: $receipt,
                    amount: (float) $plan->price,
                    currency: 'INR'
                );
                $razorpayOrderId = is_object($rpOrder) ? ($rpOrder->id ?? ($rpOrder['id'] ?? null)) : ($rpOrder['id'] ?? null);

                if ($razorpayOrderId) {
                    session([
                        'pending_prive_enrollment' => [
                            'plan_id' => $plan->id,
                            'plan_slug' => $plan->slug,
                            'razorpay_order_id' => $razorpayOrderId,
                            'amount' => (float) $plan->price,
                            'user_id' => $user->id,
                        ],
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Razorpay membership order creation failed: ' . $e->getMessage(), [
                    'plan' => $plan->slug,
                    'price' => $plan->price,
                ]);
                $razorpayError = $e->getMessage();
            }
        }

        return view('prive-club.checkout', [
            'plan' => $plan,
            'razorpayKey' => $razorpayKey,
            'razorpayOrderId' => $razorpayOrderId,
            'razorpayError' => $razorpayError,
            'user' => $user,
        ]);
    }

    /**
     * Process membership payment confirmation and issue digital privilege card.
     */
    public function processEnrollment(Request $request): JsonResponse|RedirectResponse
    {
        if (! auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        $planSlug = $request->input('plan_slug');
        $plan = MembershipPlan::where('slug', $planSlug)->active()->firstOrFail();

        // Strictly verify Razorpay payment parameters
        $paymentId = (string) $request->input('razorpay_payment_id');
        $orderId = (string) $request->input('razorpay_order_id');
        $signature = (string) $request->input('razorpay_signature');

        if (empty($paymentId) || empty($orderId) || empty($signature)) {
            $msg = 'Valid payment verification parameters are required to activate membership.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        // Prevent duplicate payment reuse
        if (Membership::where('razorpay_payment_id', $paymentId)->exists()) {
            $msg = 'This payment has already been verified for an active membership.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->route('account')->with('info', $msg);
        }

        $razorpay = app(RazorpayService::class);
        $isValid = $razorpay->verifySignature($orderId, $paymentId, $signature);
        if (! $isValid) {
            $msg = 'Payment signature verification failed. Membership cannot be activated without verified payment.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        // Verify that the order ID matches the pending session enrollment to prevent order swapping / tier manipulation
        $pending = session('pending_prive_enrollment');
        if ($pending && is_array($pending)) {
            if (($pending['user_id'] ?? null) !== $user->id || ($pending['plan_slug'] ?? null) !== $plan->slug) {
                $msg = 'Membership enrollment session does not match the requested tier plan.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg);
            }

            if (!empty($pending['razorpay_order_id']) && $pending['razorpay_order_id'] !== $orderId) {
                $msg = 'Payment order reference does not match this membership transaction.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg);
            }
        }

        try {
            $membership = DB::transaction(function () use ($user, $plan, $paymentId, $orderId): Membership {
                $now = now();
                $expires = $now->copy()->addMonths((int) ($plan->validity_months ?? 12));

                return Membership::create([
                    'user_id' => $user->id,
                    'membership_plan_id' => $plan->id,
                    'tier' => $plan->slug,
                    'tier_name' => $plan->name,
                    'fee_paid' => $plan->price,
                    'razorpay_order_id' => $orderId,
                    'razorpay_payment_id' => $paymentId,
                    'status' => 'active',
                    'starts_at' => $now,
                    'expires_at' => $expires,
                    'next_order_discount_used' => false,
                ]);
            });

            session()->forget('pending_prive_enrollment');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Welcome to VRAZIO PRIVÉ CLUB! Your digital membership card is now active.',
                    'redirect' => route('account'),
                    'member_id' => $membership->member_id,
                ]);
            }

            return redirect()->route('account')
                ->with('success', "Welcome to VRAZIO PRIVÉ CLUB! Your Member ID is {$membership->member_id}.");

        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to activate membership: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error activating membership: ' . $e->getMessage());
        }
    }
}
