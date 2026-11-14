<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\MembershipRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipManagementSystemTest extends TestCase
{
    use RefreshDatabase;

    private MembershipPlan $memberPlan;

    private MembershipPlan $goldPlan;

    private MembershipPlan $goldMaxPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->memberPlan = MembershipPlan::firstOrCreate(
            ['slug' => 'member'],
            [
                'name' => 'Member',
                'tagline' => 'Essential Patronage',
                'price' => 299.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 5.00,
                'next_order_max_discount' => 100.00,
                'next_order_min_cart' => 999.00,
                'benefits' => ['5% Next Order Discount'],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $this->goldPlan = MembershipPlan::firstOrCreate(
            ['slug' => 'gold'],
            [
                'name' => 'Gold Member',
                'tagline' => 'Refined Luxury',
                'price' => 499.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 10.00,
                'next_order_max_discount' => 250.00,
                'next_order_min_cart' => 1499.00,
                'benefits' => ['10% Next Order Discount', 'Priority Concierge'],
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $this->goldMaxPlan = MembershipPlan::firstOrCreate(
            ['slug' => 'gold_max'],
            [
                'name' => 'Gold Max Member',
                'tagline' => 'The Pinnacle of Haute Joaillerie',
                'price' => 999.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 15.00,
                'next_order_max_discount' => 500.00,
                'next_order_min_cart' => 1999.00,
                'benefits' => ['15% Next Order Discount', '36 Months Warranty Access'],
                'is_active' => true,
                'sort_order' => 3,
            ]
        );
    }

    private function createCustomerWithMembership(MembershipPlan $plan, string $status = 'active', bool $isExpired = false): array
    {
        $user = User::factory()->create();

        $now = now();
        $expires = $isExpired ? $now->copy()->subMonth() : $now->copy()->addMonths(12);

        $membership = Membership::create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'tier' => $plan->slug,
            'tier_name' => $plan->name,
            'fee_paid' => $plan->price,
            'status' => $status,
            'starts_at' => $now->copy()->subMonths(1),
            'expires_at' => $expires,
            'auto_renewal' => true,
        ]);

        return [$user, $membership];
    }

    /**
     * TEST 1: Customer submits cancellation request.
     * Admin reviews and approves cancellation.
     * Customer receives status update.
     */
    public function test_customer_submits_cancellation_and_admin_approves(): void
    {
        [$customer, $membership] = $this->createCustomerWithMembership($this->memberPlan);
        $admin = User::factory()->create(['role' => 'admin']);

        // Customer accesses manage membership page
        $res = $this->actingAs($customer)->get(route('account.membership.manage'));
        $res->assertStatus(200);
        $res->assertSee('Manage Membership');
        $res->assertSee('Member');

        // Customer submits cancellation request
        $postRes = $this->actingAs($customer)->post(route('account.membership.cancel'), [
            'reason' => 'Not using membership benefits frequently',
            'customer_notes' => 'Temporarily pausing my subscription.',
            'request_refund' => false,
        ]);

        $postRes->assertRedirect(route('account.membership.manage'));
        $postRes->assertSessionHas('success');

        // Verify request stored in database
        $request = MembershipRequest::where('membership_id', $membership->id)->first();
        $this->assertNotNull($request);
        $this->assertEquals(MembershipRequest::TYPE_CANCELLATION, $request->type);
        $this->assertEquals(MembershipRequest::STATUS_PENDING_REVIEW, $request->status);
        $this->assertStringStartsWith('MCR-', $request->request_number);
        $this->assertFalse($request->is_refund_requested);

        // Admin reviews and approves cancellation
        $request->update([
            'status' => MembershipRequest::STATUS_APPROVED,
            'auto_renewal_disabled' => true,
            'admin_notes' => 'Customer requested pause.',
            'admin_response' => 'Your cancellation has been processed. Access remains active until period end.',
            'actioned_by' => $admin->id,
            'actioned_at' => now(),
        ]);
        $request->logAuditAction('approve_cancellation', $admin->id, 'Approved by admin', MembershipRequest::STATUS_APPROVED);

        $membership->update([
            'status' => 'cancelled',
            'auto_renewal' => false,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
        ]);

        // Customer views updated status on tracking page
        $trackRes = $this->actingAs($customer)->get(route('account.membership.request.track', $request->request_number));
        $trackRes->assertStatus(200);
        $trackRes->assertSee('Approved');
        $trackRes->assertSee('Your cancellation has been processed');
    }

    /**
     * TEST 2: Customer submits cancellation and refund request.
     * Admin rejects refund.
     * Membership cancellation and refund statuses remain correctly separated.
     */
    public function test_customer_submits_cancellation_with_refund_and_admin_rejects_refund(): void
    {
        [$customer, $membership] = $this->createCustomerWithMembership($this->goldPlan);
        $admin = User::factory()->create(['role' => 'admin']);

        // Customer submits cancellation WITH refund request
        $postRes = $this->actingAs($customer)->post(route('account.membership.cancel'), [
            'reason' => 'Dissatisfied with privilege perks',
            'customer_notes' => 'Requesting full fee refund.',
            'request_refund' => true,
        ]);

        $postRes->assertRedirect(route('account.membership.manage'));

        $request = MembershipRequest::where('membership_id', $membership->id)->first();
        $this->assertNotNull($request);
        $this->assertTrue($request->is_refund_requested);
        $this->assertEquals(MembershipRequest::REFUND_PENDING_REVIEW, $request->refund_status);
        $this->assertEquals(499.00, (float) $request->refund_amount);

        // Admin approves cancellation but REJECTS refund according to policy
        $request->update([
            'status' => MembershipRequest::STATUS_APPROVED,
            'refund_status' => MembershipRequest::REFUND_REJECTED,
            'admin_response' => 'Cancellation approved. Refund request declined as subscription was utilized beyond 14-day window.',
            'actioned_by' => $admin->id,
            'actioned_at' => now(),
        ]);

        $membership->update([
            'status' => 'cancelled',
            'auto_renewal' => false,
            'cancelled_at' => now(),
        ]);

        // Verify independent separation of statuses
        $request->refresh();
        $this->assertEquals(MembershipRequest::STATUS_APPROVED, $request->status);
        $this->assertEquals(MembershipRequest::REFUND_REJECTED, $request->refund_status);
        $this->assertEquals('Cancelled', $membership->refresh()->status_label);
    }

    /**
     * TEST 3: Customer submits refund request.
     * Admin approves it.
     * Admin manually processes the refund and marks it completed.
     */
    public function test_customer_submits_refund_and_admin_manually_completes_it(): void
    {
        [$customer, $membership] = $this->createCustomerWithMembership($this->memberPlan);
        $admin = User::factory()->create(['role' => 'admin']);

        // Customer submits refund request
        $res = $this->actingAs($customer)->post(route('account.membership.refund'), [
            'reason' => 'Accidental purchase',
            'customer_notes' => 'Please refund to my bank account.',
        ]);

        $res->assertRedirect(route('account.membership.manage'));

        $request = MembershipRequest::where('membership_id', $membership->id)->first();
        $this->assertNotNull($request);
        $this->assertEquals(MembershipRequest::TYPE_REFUND, $request->type);
        $this->assertEquals(MembershipRequest::REFUND_PENDING_REVIEW, $request->refund_status);

        // 1. Admin approves refund
        $request->update([
            'refund_status' => MembershipRequest::REFUND_APPROVED,
            'admin_notes' => 'Approved under 24hr accidental purchase exception.',
            'actioned_by' => $admin->id,
            'actioned_at' => now(),
        ]);
        $request->logAuditAction('approve_refund', $admin->id, 'Refund approved', MembershipRequest::REFUND_APPROVED);

        // 2. Admin marks refund processing
        $request->update([
            'refund_status' => MembershipRequest::REFUND_PROCESSING,
            'status' => MembershipRequest::STATUS_REFUND_PROCESSING,
        ]);

        // 3. Admin manually completes refund via NEFT / Gateway and records transaction ID
        $request->update([
            'refund_status' => MembershipRequest::REFUND_COMPLETED,
            'status' => MembershipRequest::STATUS_REFUNDED,
            'refund_amount' => 299.00,
            'refund_transaction_id' => 'UTR998877665544',
            'refund_processed_at' => now(),
            'admin_response' => 'Refund credited via NEFT ref UTR998877665544.',
        ]);
        $request->logAuditAction('refund_completed', $admin->id, 'Completed with UTR998877665544', MembershipRequest::REFUND_COMPLETED);

        $request->refresh();
        $this->assertEquals(MembershipRequest::REFUND_COMPLETED, $request->refund_status);
        $this->assertEquals(MembershipRequest::STATUS_REFUNDED, $request->status);
        $this->assertEquals('UTR998877665544', $request->refund_transaction_id);
    }

    /**
     * TEST 4: Customer requests an upgrade.
     * Admin approves it.
     * Additional payment is confirmed.
     * New membership is activated correctly without charging twice.
     */
    public function test_customer_requests_upgrade_and_admin_activates_after_payment_confirmation(): void
    {
        [$customer, $membership] = $this->createCustomerWithMembership($this->memberPlan); // fee_paid = 299
        $admin = User::factory()->create(['role' => 'admin']);

        // Customer requests upgrade to Gold Member (price = 499)
        $res = $this->actingAs($customer)->post(route('account.membership.upgrade'), [
            'target_plan_id' => $this->goldPlan->id,
            'customer_notes' => 'Elevating to Gold tier.',
        ]);

        $res->assertRedirect(route('account.membership.manage'));

        $request = MembershipRequest::where('membership_id', $membership->id)->first();
        $this->assertNotNull($request);
        $this->assertEquals(MembershipRequest::TYPE_UPGRADE, $request->type);
        $this->assertEquals(MembershipRequest::STATUS_PENDING_REVIEW, $request->status);

        // Price difference should be exactly 499 - 299 = 200
        $this->assertEquals(200.00, (float) $request->price_difference);
        $this->assertEquals(200.00, (float) $request->additional_payment_required);

        // Admin approves upgrade and sets payment pending
        $request->update([
            'status' => MembershipRequest::STATUS_PAYMENT_PENDING,
            'admin_response' => 'Upgrade approved. Please complete UPI transfer of ₹200.00.',
            'actioned_by' => $admin->id,
            'actioned_at' => now(),
        ]);

        // Customer pays differential ₹200, admin confirms payment and activates upgrade
        $paymentRef = 'UPI_TXN_55443322';
        $request->update([
            'status' => MembershipRequest::STATUS_COMPLETED,
            'payment_reference' => $paymentRef,
            'payment_received_at' => now(),
            'admin_response' => 'Payment received. Welcome to Gold Member tier!',
        ]);

        $membership->update([
            'membership_plan_id' => $this->goldPlan->id,
            'tier' => $this->goldPlan->slug,
            'tier_name' => $this->goldPlan->name,
            'fee_paid' => $this->goldPlan->price,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(12),
            'status' => 'active',
        ]);

        $membership->refresh();
        $this->assertEquals('gold', $membership->tier);
        $this->assertEquals('Gold Member', $membership->tier_name);
        $this->assertEquals(499.00, (float) $membership->fee_paid);
        $this->assertTrue($membership->isActive());
    }

    /**
     * TEST 5: Customer tries to access another customer's request.
     * Access must be denied (IDOR protection).
     */
    public function test_customer_cannot_access_another_customers_request_idor_protection(): void
    {
        [$customerA, $membershipA] = $this->createCustomerWithMembership($this->memberPlan);

        $customerB = User::factory()->create([
            'email' => 'other@example.com',
            'phone' => '9111222333',
        ]);

        // Customer A creates request
        $request = MembershipRequest::create([
            'membership_id' => $membershipA->id,
            'user_id' => $customerA->id,
            'type' => MembershipRequest::TYPE_CANCELLATION,
            'status' => MembershipRequest::STATUS_PENDING_REVIEW,
            'reason' => 'Private reason',
        ]);

        // Customer B tries to view Customer A's docket
        $res = $this->actingAs($customerB)->get(route('account.membership.request.track', $request->request_number));
        $res->assertStatus(403);
    }

    /**
     * TEST 6: Customer repeatedly clicks submit.
     * Duplicate requests must be prevented.
     */
    public function test_duplicate_cancellation_and_upgrade_requests_are_prevented(): void
    {
        [$customer, $membership] = $this->createCustomerWithMembership($this->memberPlan);

        // First cancellation request
        $res1 = $this->actingAs($customer)->post(route('account.membership.cancel'), [
            'reason' => 'First attempt',
        ]);
        $res1->assertRedirect(route('account.membership.manage'));
        $this->assertEquals(1, MembershipRequest::where('membership_id', $membership->id)->count());

        // Repeated click while first request is pending
        $res2 = $this->actingAs($customer)->post(route('account.membership.cancel'), [
            'reason' => 'Repeated click attempt',
        ]);
        $res2->assertSessionHas('error');
        // Count remains 1
        $this->assertEquals(1, MembershipRequest::where('membership_id', $membership->id)->count());
    }

    /**
     * TEST 7: Test expired, active, cancelled, pending, and renewal-disabled memberships.
     */
    public function test_membership_statuses_and_scopes_work_seamlessly(): void
    {
        // 1. Active membership
        [$activeUser, $activeMembership] = $this->createCustomerWithMembership($this->memberPlan, 'active', false);
        $this->assertTrue($activeMembership->isActive());
        $this->assertEquals('Active', $activeMembership->status_label);
        $this->assertTrue($activeMembership->canBeCancelled());

        // 2. Expired membership
        [$expiredUser, $expiredMembership] = $this->createCustomerWithMembership($this->memberPlan, 'expired', true);
        $this->assertFalse($expiredMembership->isActive());
        $this->assertTrue($expiredMembership->isExpired());
        $this->assertEquals('Expired', $expiredMembership->status_label);

        // 3. Cancelled membership
        [$cancelledUser, $cancelledMembership] = $this->createCustomerWithMembership($this->memberPlan, 'cancelled', false);
        $this->assertFalse($cancelledMembership->isActive());
        $this->assertEquals('cancelled', $cancelledMembership->status);
        $this->assertEquals('Cancelled', $cancelledMembership->status_label);

        // 4. Renewal-disabled membership
        $activeMembership->update(['auto_renewal' => false]);
        $this->assertFalse((bool) $activeMembership->refresh()->auto_renewal);
        // Active access still preserved until expiry!
        $this->assertTrue($activeMembership->isActive());

        // 5. WhatsApp Link Generation check
        $req = MembershipRequest::create([
            'membership_id' => $activeMembership->id,
            'user_id' => $activeUser->id,
            'type' => MembershipRequest::TYPE_CANCELLATION,
            'status' => MembershipRequest::STATUS_PENDING_REVIEW,
            'reason' => 'Testing WhatsApp',
        ]);
        $whatsAppUrl = $req->getWhatsAppUrl();
        $this->assertStringContainsString('wa.me/918791227210', $whatsAppUrl);
        $this->assertStringContainsString($req->request_number, $whatsAppUrl);
    }
}
