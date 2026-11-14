<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WarrantyClaimSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createDeliveredOrderWithPriveMember(): array
    {
        $user = User::factory()->create([
            'email' => 'patron@example.com',
            'phone' => '9999999999',
        ]);

        $plan = MembershipPlan::firstOrCreate(
            ['slug' => 'prive-gold'],
            [
                'name' => 'Gold Member',
                'price' => 999.00,
                'tagline' => 'Luxury Patron Tier',
                'benefits' => ['Free Shipping', 'Warranty Protection'],
                'validity_months' => 12,
                'next_order_discount_percent' => 5.0,
                'next_order_max_discount' => 500.0,
                'next_order_min_cart' => 1000.0,
                'birthday_discount_percent' => 5.0,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Membership::create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'tier' => 'gold',
            'tier_name' => 'Gold Member',
            'member_id' => 'VPC-TEST-001',
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addYear(),
            'fee_paid' => 999.00,
        ]);

        $product = Product::factory()->create([
            'title' => 'Padmavati Gold Saree Pin',
            'warranty_period_months' => 24,
        ]);

        $order = Order::create([
            'order_number' => 'VRZ-WARR001',
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => '9999999999',
            'shipping_address' => ['street' => 'Atelier Suite 101', 'city' => 'Jaipur', 'state' => 'Rajasthan', 'pincode' => '302001'],
            'shipping_method' => 'standard',
            'subtotal' => 50000.0,
            'tax_gst' => 1500.0,
            'shipping_cost' => 0.0,
            'total_amount' => 51500.0,
            'payment_gateway' => 'cod',
            'payment_status' => 'completed',
            'fulfillment_status' => 'delivered',
            'delivered_at' => now()->subDays(5),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_sku' => $product->sku ?? 'VRZ-TEST',
            'quantity' => 1,
            'unit_price' => 50000.0,
            'line_total' => 50000.0,
        ]);

        return [$user, $order, $product];
    }

    public function test_user_can_submit_warranty_claim_with_single_photo(): void
    {
        Storage::fake('local');

        [$user, $order, $product] = $this->createDeliveredOrderWithPriveMember();

        // Valid image
        $photo = UploadedFile::fake()->image('defect.jpg', 600, 600)->size(1024);

        $payload = [
            'order_id' => $order->id,
            'customer_name' => $user->name,
            'phone' => '9999999999',
            'email' => $user->email,
            'product_name' => $product->title,
            'issue_type' => 'stone_loose_displaced',
            'description' => 'The central prong appears slightly loose after normal gala wear.',
            'photos' => [$photo],
        ];

        $response = $this->actingAs($user)->post(route('warranty.store'), $payload);

        $response->assertSessionHasNoErrors();

        $claim = WarrantyClaim::where('order_id', $order->id)->first();
        $this->assertNotNull($claim);
        $this->assertEquals('submitted', $claim->status);

        // Verify image was saved in database without NOT NULL error
        $claimImage = WarrantyClaimImage::where('claim_id', $claim->id)->first();
        $this->assertNotNull($claimImage);
        $this->assertNotEmpty($claimImage->image_path);
        $this->assertEquals($claimImage->image_path, $claimImage->file_path);

        // Verify image file was written to private storage disk
        Storage::disk('local')->assertExists($claimImage->image_path);
    }

    public function test_fake_image_or_script_upload_is_rejected_by_security_filter(): void
    {
        Storage::fake('local');

        [$user, $order, $product] = $this->createDeliveredOrderWithPriveMember();

        // Malicious fake file: PHP script masked as .jpg
        $fakeScript = UploadedFile::fake()->createWithContent('exploit.jpg', '<?php phpinfo(); ?>');

        $payload = [
            'order_id' => $order->id,
            'customer_name' => $user->name,
            'phone' => '9999999999',
            'email' => $user->email,
            'product_name' => $product->title,
            'issue_type' => 'stone_loose_displaced',
            'description' => 'Malicious payload testing for image upload security.',
            'photos' => [$fakeScript],
        ];

        $response = $this->actingAs($user)->post(route('warranty.store'), $payload);

        // Claim must NOT be created
        $this->assertDatabaseMissing('warranty_claims', ['order_id' => $order->id]);
        $response->assertSessionHas('error');
    }
}
