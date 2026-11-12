<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Membership Plans Table
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->string('tagline', 255)->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('validity_months')->default(12);
            $table->decimal('next_order_discount_percent', 5, 2);
            $table->decimal('next_order_max_discount', 10, 2);
            $table->decimal('next_order_min_cart', 10, 2);
            $table->decimal('birthday_discount_percent', 5, 2)->default(0);
            $table->decimal('birthday_max_discount', 10, 2)->default(0);
            $table->decimal('birthday_min_cart', 10, 2)->default(0);
            $table->json('benefits');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        // 2. Memberships Table
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('membership_plan_id')->constrained('membership_plans')->cascadeOnDelete();
            $table->string('member_id', 50)->unique()->index(); // e.g. VPC-2026-00001
            $table->string('tier', 50)->index(); // member, gold, gold_max
            $table->string('tier_name', 100);
            $table->decimal('fee_paid', 10, 2);
            $table->string('razorpay_order_id', 100)->nullable();
            $table->string('razorpay_payment_id', 100)->nullable();
            $table->string('status', 30)->default('active')->index(); // active, expired, suspended, cancelled
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->index();
            $table->boolean('next_order_discount_used')->default(false);
            $table->timestamp('next_order_discount_used_at')->nullable();
            $table->foreignId('next_order_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedSmallInteger('birthday_coupon_used_year')->nullable();
            $table->string('card_token', 100)->unique(); // Secure QR verification token
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // 3. User Date of Birth for Birthday Privileges
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('phone');
        });

        // 4. Order Privé Discount Tracking
        Schema::table('orders', function (Blueprint $table) {
            $table->string('prive_member_id', 50)->nullable()->after('voucher_code');
            $table->decimal('prive_discount_amount', 10, 2)->default(0)->after('discount_amount');
        });

        // 5. Warranty Claims Enhancements for Members-Only Access
        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->string('member_id', 50)->nullable()->after('user_id');
            $table->foreignId('product_id')->nullable()->after('member_id')->constrained('products')->nullOnDelete();
            $table->string('product_sku', 50)->nullable()->after('product_name');
            $table->string('remedy_requested', 100)->nullable()->after('issue_type');
            $table->string('admin_remedy', 100)->nullable()->after('remedy_requested');
        });

        // 6. Seed the Three Official VRAZIO PRIVÉ CLUB Plans
        DB::table('membership_plans')->insert([
            [
                'slug' => 'member',
                'name' => 'Member',
                'tagline' => 'Essential Atelier Privileges',
                'price' => 299.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 5.00,
                'next_order_max_discount' => 100.00,
                'next_order_min_cart' => 999.00,
                'birthday_discount_percent' => 0.00,
                'birthday_max_discount' => 0.00,
                'birthday_min_cart' => 0.00,
                'benefits' => json_encode([
                    '5% discount on the next eligible order (Max ₹100, Min cart ₹999)',
                    'Members-only warranty claim access on eligible products',
                    'Personalised digital membership card',
                    'Early access to selected launches and member-only offers',
                    'Member support queue',
                ]),
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'gold',
                'name' => 'Gold Member',
                'tagline' => 'Signature Luxury & Celebration Privileges',
                'price' => 499.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 10.00,
                'next_order_max_discount' => 250.00,
                'next_order_min_cart' => 1499.00,
                'birthday_discount_percent' => 10.00,
                'birthday_max_discount' => 150.00,
                'birthday_min_cart' => 1499.00,
                'benefits' => json_encode([
                    '10% discount on the next eligible order (Max ₹250, Min cart ₹1,499)',
                    'Members-only warranty claim access on eligible products',
                    'Premium digital membership card with gold styling',
                    'Priority customer support',
                    'Early access to selected launches',
                    'Birthday coupon: 10% discount (Max ₹150, Min cart ₹1,499 in birthday month)',
                    'Gold member-only exclusive offers',
                ]),
                'is_active' => true,
                'is_featured' => true, // Highlighted Recommended
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'gold_max',
                'name' => 'Gold Max Member',
                'tagline' => 'The Sovereign Apex of Haute Joaillerie & Couture',
                'price' => 999.00,
                'validity_months' => 12,
                'next_order_discount_percent' => 15.00,
                'next_order_max_discount' => 500.00,
                'next_order_min_cart' => 1999.00,
                'birthday_discount_percent' => 15.00,
                'birthday_max_discount' => 300.00,
                'birthday_min_cart' => 1999.00,
                'benefits' => json_encode([
                    '15% discount on the next eligible order (Max ₹500, Min cart ₹1,999)',
                    'Members-only warranty claim access on eligible products',
                    'Exclusive premium digital membership card',
                    'Highest priority customer support',
                    'First access to selected launches and limited collections',
                    'Birthday coupon: 15% discount (Max ₹300, Min cart ₹1,999 in birthday month)',
                    'Gold Max-only offers and occasional member surprises',
                ]),
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('membership_plans');

        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['member_id', 'product_id', 'product_sku', 'remedy_requested', 'admin_remedy']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['prive_member_id', 'prive_discount_amount']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth']);
        });
    }
};
