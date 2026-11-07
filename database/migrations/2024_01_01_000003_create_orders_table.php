<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique(); // 'VRZ-XXXXXXXX'
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone', 15);
            $table->json('shipping_address');
            $table->enum('shipping_method', ['standard', 'express'])->default('standard');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_gst', 12, 2); // 3%
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_gateway', 20)->default('razorpay');
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('fulfillment_status', ['inscribed', 'hallmarking', 'armoured_transit', 'delivered'])->default('inscribed');
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamps();
            $table->index('payment_status');
            $table->index('fulfillment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
