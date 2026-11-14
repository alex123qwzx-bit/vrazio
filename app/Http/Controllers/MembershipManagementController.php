<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\MembershipRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipManagementController extends Controller
{
    /**
     * Display the Manage Membership customer portal.
     */
    public function showManage(): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('info', 'Please sign in to manage your VRAZIO PRIVÉ CLUB membership.');
        }

        $user = auth()->user();
        $membership = $user->activeMembership() ?? $user->memberships()->latest()->first();

        if (! $membership) {
            return redirect()->route('prive-club.index')->with('info', 'You do not have an active membership yet. Explore our luxury atelier privilege tiers below.');
        }

        $plan = $membership->plan;

        // Fetch higher-tier plans available for upgrade
        $higherPlans = MembershipPlan::active()
            ->where('price', '>', (float) $membership->fee_paid)
            ->orderBy('price', 'asc')
            ->get()
            ->map(function (MembershipPlan $higherPlan) use ($membership) {
                $diff = max(0.00, (float) $higherPlan->price - (float) $membership->fee_paid);
                $higherPlan->price_difference = $diff;
                $higherPlan->formatted_difference = '₹'.number_format($diff, 2);

                return $higherPlan;
            });

        // Request history for this user
        $requests = MembershipRequest::where('user_id', $user->id)
            ->with(['membership', 'currentPlan', 'targetPlan'])
            ->latest()
            ->get();

        return view('account.membership.manage', [
            'user' => $user,
            'membership' => $membership,
            'plan' => $plan,
            'higherPlans' => $higherPlans,
            'requests' => $requests,
        ]);
    }

    /**
     * Submit a cancellation request (with optional refund request).
     */
    public function requestCancellation(Request $request): JsonResponse|RedirectResponse
    {
        if (! auth()->check()) {
            return $this->errorResponse($request, 'Unauthorized. Please sign in.', 401);
        }

        $user = auth()->user();
        $membership = $user->activeMembership();

        if (! $membership) {
            return $this->errorResponse($request, 'You do not have an active membership eligible for cancellation.', 422);
        }

        // Prevent duplicate concurrent requests
        if ($membership->isCancellationPending()) {
            $existing = $membership->activeRequest();
            $msg = 'A cancellation request for this membership is already under administrative review (Docket #'.($existing?->request_number ?? '').').';

            return $this->errorResponse($request, $msg, 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'customer_notes' => ['nullable', 'string', 'max:1500'],
            'request_refund' => ['nullable', 'boolean'],
        ]);

        $isRefundRequested = $request->boolean('request_refund');

        $req = DB::transaction(function () use ($user, $membership, $validated, $isRefundRequested): MembershipRequest {
            return MembershipRequest::create([
                'membership_id' => $membership->id,
                'user_id' => $user->id,
                'type' => MembershipRequest::TYPE_CANCELLATION,
                'status' => MembershipRequest::STATUS_PENDING_REVIEW,
                'reason' => $validated['reason'],
                'customer_notes' => $validated['customer_notes'] ?? null,
                'is_refund_requested' => $isRefundRequested,
                'refund_amount' => $isRefundRequested ? (float) $membership->fee_paid : 0.00,
                'refund_status' => $isRefundRequested ? MembershipRequest::REFUND_PENDING_REVIEW : null,
                'current_plan_id' => $membership->membership_plan_id,
                'previous_membership_details' => [
                    'member_id' => $membership->member_id,
                    'tier' => $membership->tier,
                    'tier_name' => $membership->tier_name,
                    'fee_paid' => (float) $membership->fee_paid,
                    'starts_at' => $membership->starts_at?->toIso8601String(),
                    'expires_at' => $membership->expires_at?->toIso8601String(),
                    'auto_renewal' => (bool) $membership->auto_renewal,
                ],
            ]);
        });

        $msg = "Your cancellation request (#{$req->request_number}) has been submitted for administrative review. Our atelier concierge will review your docket.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'request_number' => $req->request_number,
                'redirect' => route('account.membership.manage'),
            ]);
        }

        return redirect()->route('account.membership.manage')->with('success', $msg);
    }

    /**
     * Submit a standalone refund review request.
     */
    public function requestRefund(Request $request): JsonResponse|RedirectResponse
    {
        if (! auth()->check()) {
            return $this->errorResponse($request, 'Unauthorized. Please sign in.', 401);
        }

        $user = auth()->user();
        $membership = $user->activeMembership() ?? $user->memberships()->latest()->first();

        if (! $membership) {
            return $this->errorResponse($request, 'No membership record located for refund review.', 422);
        }

        if ($membership->isRefundPending()) {
            return $this->errorResponse($request, 'A refund request for this membership is already actively under review.', 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'customer_notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $req = DB::transaction(function () use ($user, $membership, $validated): MembershipRequest {
            return MembershipRequest::create([
                'membership_id' => $membership->id,
                'user_id' => $user->id,
                'type' => MembershipRequest::TYPE_REFUND,
                'status' => MembershipRequest::STATUS_PENDING_REVIEW,
                'reason' => $validated['reason'],
                'customer_notes' => $validated['customer_notes'] ?? null,
                'is_refund_requested' => true,
                'refund_amount' => (float) $membership->fee_paid,
                'refund_status' => MembershipRequest::REFUND_PENDING_REVIEW,
                'current_plan_id' => $membership->membership_plan_id,
                'previous_membership_details' => [
                    'member_id' => $membership->member_id,
                    'tier' => $membership->tier,
                    'tier_name' => $membership->tier_name,
                    'fee_paid' => (float) $membership->fee_paid,
                ],
            ]);
        });

        $msg = "Your refund request (#{$req->request_number}) has been submitted for review. Refund eligibility is subject to atelier policy.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'request_number' => $req->request_number,
                'redirect' => route('account.membership.manage'),
            ]);
        }

        return redirect()->route('account.membership.manage')->with('success', $msg);
    }

    /**
     * Submit an upgrade request to a higher tier plan.
     */
    public function requestUpgrade(Request $request): JsonResponse|RedirectResponse
    {
        if (! auth()->check()) {
            return $this->errorResponse($request, 'Unauthorized. Please sign in.', 401);
        }

        $user = auth()->user();
        $membership = $user->activeMembership();

        if (! $membership) {
            return $this->errorResponse($request, 'You do not have an active membership eligible for upgrade.', 422);
        }

        if ($membership->isUpgradePending()) {
            return $this->errorResponse($request, 'An upgrade request is already under administrative review for this membership.', 422);
        }

        $validated = $request->validate([
            'target_plan_id' => ['required', 'exists:membership_plans,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'customer_notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $targetPlan = MembershipPlan::findOrFail((int) $validated['target_plan_id']);

        if ((float) $targetPlan->price <= (float) $membership->fee_paid) {
            return $this->errorResponse($request, 'The selected plan is not a higher-tier upgrade.', 422);
        }

        $priceDifference = max(0.00, (float) $targetPlan->price - (float) $membership->fee_paid);

        $req = DB::transaction(function () use ($user, $membership, $targetPlan, $priceDifference, $validated): MembershipRequest {
            return MembershipRequest::create([
                'membership_id' => $membership->id,
                'user_id' => $user->id,
                'type' => MembershipRequest::TYPE_UPGRADE,
                'status' => MembershipRequest::STATUS_PENDING_REVIEW,
                'current_plan_id' => $membership->membership_plan_id,
                'target_plan_id' => $targetPlan->id,
                'price_difference' => $priceDifference,
                'additional_payment_required' => $priceDifference,
                'reason' => $validated['reason'] ?? "Upgrade from {$membership->tier_name} to {$targetPlan->name}",
                'customer_notes' => $validated['customer_notes'] ?? null,
                'previous_membership_details' => [
                    'member_id' => $membership->member_id,
                    'tier' => $membership->tier,
                    'tier_name' => $membership->tier_name,
                    'fee_paid' => (float) $membership->fee_paid,
                    'starts_at' => $membership->starts_at?->toIso8601String(),
                    'expires_at' => $membership->expires_at?->toIso8601String(),
                ],
                'upgraded_membership_details' => [
                    'target_slug' => $targetPlan->slug,
                    'target_name' => $targetPlan->name,
                    'target_price' => (float) $targetPlan->price,
                    'price_difference' => (float) $priceDifference,
                ],
            ]);
        });

        $msg = "Your upgrade request to {$targetPlan->name} (#{$req->request_number}) has been registered. Our concierge will review your upgrade.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'request_number' => $req->request_number,
                'redirect' => route('account.membership.manage'),
            ]);
        }

        return redirect()->route('account.membership.manage')->with('success', $msg);
    }

    /**
     * Customer Request Tracking with IDOR protection.
     */
    public function trackRequest(string $requestNumber): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $membershipRequest = MembershipRequest::where('request_number', strtoupper(trim($requestNumber)))
            ->with(['membership', 'currentPlan', 'targetPlan', 'actionedBy'])
            ->firstOrFail();

        // Strict IDOR Protection: authenticated patron must own the request or be admin
        if ($membershipRequest->user_id !== auth()->id() && ! in_array(auth()->user()->role ?? '', ['admin', 'super_admin'], true)) {
            abort(403, 'Unauthorized access to membership request docket.');
        }

        return view('account.membership.track', [
            'request' => $membershipRequest,
            'membership' => $membershipRequest->membership,
            'whatsAppUrl' => $membershipRequest->getWhatsAppUrl(),
        ]);
    }

    private function errorResponse(Request $request, string $message, int $status = 400): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        return back()->with('error', $message);
    }
}
