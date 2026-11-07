<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PhoneOtp;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_filament_admin_panel_sec_02(): void
    {
        $customer = User::factory()->create([
            'role' => 'patron',
        ]);

        $response = $this->actingAs($customer)->get('/admin');

        // Non-admin patrons must be rejected with 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_admin_can_access_filament_admin_panel_sec_02(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        // Admin must be permitted to access panel (200 OK or 302 redirect to dashboard)
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_invoice_requires_order_ownership_sec_03(): void
    {
        $owner = User::factory()->create(['phone' => '9876543210']);
        $stranger = User::factory()->create(['phone' => '9123456789']);

        $order = Order::create([
            'order_number' => 'VRZ-TEST01',
            'user_id' => $owner->id,
            'client_name' => $owner->name,
            'client_email' => $owner->email,
            'client_phone' => $owner->phone,
            'shipping_address' => ['street' => 'Atelier Lane', 'city' => 'Mumbai', 'state' => 'MH', 'pincode' => '400001'],
            'shipping_method' => 'standard',
            'subtotal' => 50000,
            'tax_gst' => 1500,
            'shipping_cost' => 0,
            'total_amount' => 51500,
            'payment_status' => 'paid',
            'fulfillment_status' => 'delivered',
        ]);

        // Unauthenticated guest cannot view invoice
        $guestResponse = $this->get(route('order.invoice', $order->order_number));
        $guestResponse->assertStatus(403);

        // Stranger patron cannot view invoice
        $strangerResponse = $this->actingAs($stranger)->get(route('order.invoice', $order->order_number));
        $strangerResponse->assertStatus(403);

        // Stranger cannot download PDF
        $downloadResponse = $this->actingAs($stranger)->get(route('order.invoice.download', $order->order_number));
        $downloadResponse->assertStatus(403);

        // Owner CAN view invoice
        $ownerResponse = $this->actingAs($owner)->get(route('order.invoice', $order->order_number));
        $ownerResponse->assertStatus(200);
    }

    public function test_public_orders_search_is_removed_and_requires_login_sec_04(): void
    {
        // Unauthenticated visitor must be redirected to login, NOT allowed to search all orders
        $response = $this->get('/orders?search=%');
        $response->assertRedirect(route('login'));
    }

    public function test_otp_lockout_after_five_failed_attempts_sec_06(): void
    {
        $service = new OtpService();
        $phone = '9876543210';

        // Send OTP
        $sendResult = $service->sendOtp($phone);
        $this->assertTrue($sendResult['success']);

        $otpRecord = PhoneOtp::where('phone', $phone)->first();
        $this->assertNotNull($otpRecord);

        // Fail 4 times
        for ($i = 0; $i < 4; $i++) {
            $verifyResult = $service->verifyOtp($phone, '000000');
            $this->assertFalse($verifyResult['success']);
        }

        // 5th failed attempt should trigger lockout and delete OTP record
        $fifthAttempt = $service->verifyOtp($phone, '000000');
        $this->assertFalse($fifthAttempt['success']);
        $this->assertStringContainsString('Too many failed attempts', $fifthAttempt['message']);

        // Record must be deleted from database
        $this->assertNull(PhoneOtp::where('phone', $phone)->first());
    }

    public function test_firebase_session_rejects_missing_or_spoofed_phone_sec_01(): void
    {
        // Fake Google Identity Toolkit lookup returning a Google user without phone_number
        \Illuminate\Support\Facades\Http::fake([
            'https://identitytoolkit.googleapis.com/*' => \Illuminate\Support\Facades\Http::response([
                'users' => [
                    [
                        'localId' => 'google_uid_12345',
                        'email' => 'attacker@gmail.com',
                        'displayName' => 'Attacker',
                        // Note: NO phoneNumber attribute in the verified token
                    ]
                ]
            ], 200),
        ]);

        // Attacker attempts to pass phone=<admin's number> to hijack account
        $response = $this->postJson(route('auth.firebase.session'), [
            'idToken' => 'valid_mock_google_id_token',
            'provider' => 'phone',
            'phone' => '9999999999', // Spoofed admin phone in request body
        ]);

        // Must reject with 403 Forbidden because token does not contain phone_number
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Token does not contain a verified mobile number.',
        ]);
    }

    public function test_security_headers_are_attached_sec_11(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
