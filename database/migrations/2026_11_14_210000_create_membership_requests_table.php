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
        // 1. Additive columns to existing memberships table
        Schema::table('memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('memberships', 'auto_renewal')) {
                $table->boolean('auto_renewal')->default(false)->after('status');
            }
            if (! Schema::hasColumn('memberships', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('auto_renewal');
            }
            if (! Schema::hasColumn('memberships', 'cancellation_reason')) {
                $table->string('cancellation_reason', 255)->nullable()->after('cancelled_at');
            }
        });

        // 2. Create membership_requests table
        if (! Schema::hasTable('membership_requests')) {
            Schema::create('membership_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number', 50)->unique()->index();
                $table->foreignId('membership_id')->constrained('memberships')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type', 30)->index(); // cancellation, refund, upgrade
                $table->string('status', 40)->default('pending_review')->index(); // pending_review, under_review, approved, rejected, refund_processing, refunded, payment_pending, payment_received, completed

                // Cancellation / Reason Details
                $table->string('reason', 255);
                $table->text('customer_notes')->nullable();

                // Refund Details
                $table->boolean('is_refund_requested')->default(false);
                $table->decimal('refund_amount', 10, 2)->default(0.00);
                $table->string('refund_status', 30)->nullable()->index(); // pending_review, under_review, approved, rejected, refund_processing, refunded
                $table->string('refund_transaction_id', 100)->nullable();
                $table->timestamp('refund_processed_at')->nullable();

                // Upgrade Details
                $table->foreignId('current_plan_id')->nullable()->constrained('membership_plans')->nullOnDelete();
                $table->foreignId('target_plan_id')->nullable()->constrained('membership_plans')->nullOnDelete();
                $table->decimal('price_difference', 10, 2)->default(0.00);
                $table->decimal('additional_payment_required', 10, 2)->default(0.00);
                $table->string('payment_reference', 100)->nullable();
                $table->timestamp('payment_received_at')->nullable();
                $table->json('previous_membership_details')->nullable();
                $table->json('upgraded_membership_details')->nullable();

                // Admin Review & Audit Log
                $table->text('admin_notes')->nullable();
                $table->text('admin_response')->nullable(); // Customer-visible response
                $table->boolean('auto_renewal_disabled')->default(false);
                $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('actioned_at')->nullable();
                $table->json('audit_log')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_requests');

        Schema::table('memberships', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('memberships', 'cancellation_reason')) {
                $columnsToDrop[] = 'cancellation_reason';
            }
            if (Schema::hasColumn('memberships', 'cancelled_at')) {
                $columnsToDrop[] = 'cancelled_at';
            }
            if (Schema::hasColumn('memberships', 'auto_renewal')) {
                $columnsToDrop[] = 'auto_renewal';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
