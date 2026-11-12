<?php

declare(strict_types=1);

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
        // 1. Add cancellation and refund columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'cancellation_status')) {
                $table->string('cancellation_status', 30)->default('none')->after('fulfillment_status')->index();
            }
            if (! Schema::hasColumn('orders', 'refund_status')) {
                $table->string('refund_status', 30)->default('none')->after('cancellation_status')->index();
            }
            if (! Schema::hasColumn('orders', 'total_refunded')) {
                $table->decimal('total_refunded', 12, 2)->default(0.00)->after('refund_status');
            }
            if (! Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('total_refunded');
            }
        });

        // 2. Add item-level cancellation columns to order_items table
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'status')) {
                $table->string('status', 30)->default('active')->after('line_total')->index();
            }
            if (! Schema::hasColumn('order_items', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('order_items', 'refunded_amount')) {
                $table->decimal('refunded_amount', 12, 2)->default(0.00)->after('cancelled_at');
            }
        });

        // 3. Create order_cancellations table
        Schema::create('order_cancellations', function (Blueprint $table) {
            $table->id();
            $table->string('cancellation_number', 50)->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20)->default('full'); // 'full' or 'partial'
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->string('reason');
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('status', 30)->default('pending_review')->index(); // pending_review, approved, rejected, completed
            $table->decimal('refund_amount', 12, 2)->default(0.00);
            $table->string('refund_method', 30)->default('razorpay'); // razorpay, cod_no_refund, manual_bank_transfer, store_credit
            $table->boolean('is_stock_restored')->default(false);
            $table->timestamp('stock_restored_at')->nullable();
            $table->boolean('prive_discount_restored')->default(false);
            $table->timestamp('prive_discount_restored_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });

        // 4. Create order_refunds table
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number', 50)->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_cancellation_id')->nullable()->constrained('order_cancellations')->cascadeOnDelete();
            $table->string('gateway', 30)->default('razorpay'); // razorpay, cod, manual
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_refund_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('status', 30)->default('pending_approval')->index(); // pending_approval, initiated, processing, refunded, failed
            $table->text('failure_reason')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // 5. Create credit_notes table (for GST compliance)
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number', 50)->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_cancellation_id')->nullable()->constrained('order_cancellations')->nullOnDelete();
            $table->string('original_invoice_number');
            $table->date('invoice_date');
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->json('client_address')->nullable();
            $table->json('cancelled_items');
            $table->decimal('taxable_amount', 12, 2)->default(0.00);
            $table->decimal('gst_amount', 12, 2)->default(0.00);
            $table->decimal('total_credit_amount', 12, 2)->default(0.00);
            $table->string('reason');
            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('order_refunds');
        Schema::dropIfExists('order_cancellations');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'cancelled_at', 'refunded_amount']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cancellation_status', 'refund_status', 'total_refunded', 'cancelled_at']);
        });
    }
};
