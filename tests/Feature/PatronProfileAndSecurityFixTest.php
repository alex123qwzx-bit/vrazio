<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PatronProfileAndSecurityFixTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_oauth_saves_long_avatar_url_without_truncation(): void
    {
        $longAvatarUrl = 'https://lh3.googleusercontent.com/a-/' . Str::random(400) . '=s96-c';
        $googleUid = 'google_uid_' . Str::random(16);

        Http::fake([
            'https://identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [
                    [
                        'localId' => $googleUid,
                        'email' => 'patron@example.com',
                        'displayName' => 'Patron Tester',
                        'photoUrl' => $longAvatarUrl,
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('auth.firebase.session'), [
            'idToken' => 'mock_google_token',
            'provider' => 'google',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user = User::where('google_id', $googleUid)->first();
        $this->assertNotNull($user);
        $this->assertEquals($longAvatarUrl, $user->avatar);
        $this->assertTrue(strlen($user->avatar) > 255);
    }

    public function test_order_fulfillment_status_can_be_updated_to_packed_and_casual_steps(): void
    {
        $user = User::factory()->create(['phone' => '9876543210']);

        $order = Order::create([
            'order_number' => 'VRZ-TEST09',
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => $user->phone,
            'shipping_address' => ['street' => 'Test Street', 'city' => 'Moradabad', 'state' => 'UP', 'pincode' => '244001'],
            'shipping_method' => 'standard',
            'subtotal' => 840000,
            'tax_gst' => 25200,
            'shipping_cost' => 0,
            'total_amount' => 865200,
            'payment_status' => 'pending',
            'fulfillment_status' => 'inscribed',
        ]);

        // Update to packed (the exact step that previously failed with check constraint)
        $result = $order->updateTrackingStatus('packed');
        $this->assertTrue($result);
        $this->assertEquals('packed', $order->fresh()->fulfillment_status);
        $this->assertNotNull($order->fresh()->packed_at);

        // Update to shipped
        $order->updateTrackingStatus('shipped');
        $this->assertEquals('shipped', $order->fresh()->fulfillment_status);

        // Update to out_for_delivery
        $order->updateTrackingStatus('out_for_delivery');
        $this->assertEquals('out_for_delivery', $order->fresh()->fulfillment_status);

        // Advance to delivered
        $order->advanceFulfillment();
        $this->assertEquals('delivered', $order->fresh()->fulfillment_status);
    }

    public function test_patron_can_edit_profile_name_and_phone(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'phone' => '9876543210',
            'phone_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => 'Updated Royal Patron',
            'phone' => '9123456789',
        ]);

        $response->assertRedirect(route('account'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Updated Royal Patron', $user->name);
        $this->assertEquals('9123456789', $user->phone);
        // Phone verification must be reset when number is changed
        $this->assertNull($user->phone_verified_at);
    }

    public function test_patron_cannot_take_another_users_phone_number(): void
    {
        User::factory()->create(['phone' => '9999999999']);
        $user = User::factory()->create(['phone' => '9876543210']);

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => $user->name,
            'phone' => '9999999999',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertEquals('9876543210', $user->fresh()->phone);
    }

    public function test_patron_can_update_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-secret-password'),
        ]);

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => $user->name,
            'current_password' => 'old-secret-password',
            'new_password' => 'new-secret-password-123',
            'new_password_confirmation' => 'new-secret-password-123',
        ]);

        $response->assertRedirect(route('account'));
        $this->assertTrue(Hash::check('new-secret-password-123', $user->fresh()->password));
    }

    public function test_patron_cannot_update_password_with_invalid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-secret-password'),
        ]);

        $response = $this->actingAs($user)->post(route('account.profile.update'), [
            'name' => $user->name,
            'current_password' => 'wrong-password',
            'new_password' => 'new-secret-password-123',
            'new_password_confirmation' => 'new-secret-password-123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('old-secret-password', $user->fresh()->password));
    }

    public function test_unhandled_server_exceptions_on_json_endpoints_mask_code_and_sql(): void
    {
        $user = User::factory()->create();

        // Cause a route to trigger a 500 error and verify sensitive info is masked
        $this->app['router']->get('/test-500-route', function () {
            throw new \Illuminate\Database\QueryException(
                'pgsql',
                'SELECT * FROM secret_financial_table WHERE password = 123',
                [],
                new \Exception('Fatal database table lock')
            );
        });

        $response = $this->actingAs($user)->getJson('/test-500-route');

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'We made a mistake. Our team has been notified. Please try again shortly.',
        ]);

        // Ensure raw SQL and table names are NOT present in output
        $content = $response->getContent();
        $this->assertStringNotContainsString('secret_financial_table', $content);
        $this->assertStringNotContainsString('QueryException', $content);
        $this->assertStringNotContainsString('Fatal database table lock', $content);
    }

    public function test_otp_service_does_not_leak_dev_otp_in_production(): void
    {
        config(['services.otp.dev_mode' => false]);
        config(['services.fast2sms.key' => 'dummy_key']);

        Http::fake([
            'https://www.fast2sms.com/*' => Http::response([
                'status_code' => 996,
                'message' => 'Before using OTP Message API, complete website verification. Visit OTP Message menu or use DLT SMS API.',
            ], 200),
        ]);

        $otpService = app(OtpService::class);
        $result = $otpService->sendOtp('9876543210');

        // Must NOT succeed or return dev_otp when domain is unverified in production
        $this->assertFalse($result['success']);
        $this->assertArrayNotHasKey('dev_otp', $result);
        $this->assertStringContainsString('SMS service is temporarily unavailable', $result['message']);
    }

    public function test_google_oauth2_tokeninfo_fallback_authenticates_patron_seamlessly(): void
    {
        $googleSub = 'google_sub_' . Str::random(16);
        $patronEmail = 'oauth_patron_' . Str::random(8) . '@gmail.com';

        // Identity Toolkit returns 400/failure (e.g. for raw Google OAuth or GIS tokens)
        Http::fake([
            'https://identitytoolkit.googleapis.com/*' => Http::response(['error' => ['message' => 'INVALID_ID_TOKEN']], 400),
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'sub' => $googleSub,
                'email' => $patronEmail,
                'name' => 'Royal Patron OAuth',
                'picture' => 'https://lh3.googleusercontent.com/avatar_test.jpg',
            ], 200),
        ]);

        $response = $this->postJson(route('auth.firebase.session'), [
            'idToken' => 'raw_google_gsi_id_token',
            'provider' => 'google',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user = User::where('google_id', $googleSub)->first();
        $this->assertNotNull($user);
        $this->assertEquals(strtolower($patronEmail), $user->email);
        $this->assertEquals('Royal Patron OAuth', $user->name);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_page_renders_with_resilient_google_auth_and_csp(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Continue with Google');
        $response->assertSee('accounts.google.com/gsi/client');
        $response->assertSee('getRedirectResult');
        $response->assertSee('signInWithRedirect');
    }
}
