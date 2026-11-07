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
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number', 30)->unique();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name', 150);
            $table->string('phone', 20);
            $table->string('email', 150);
            $table->string('product_name', 255)->nullable();
            $table->string('issue_type', 100);
            $table->text('description');
            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected', 'resolved'])->default('submitted');
            $table->boolean('is_checked')->default(false);
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('is_checked');
        });

        Schema::create('warranty_claim_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('warranty_claims')->cascadeOnDelete();
            $table->string('image_path', 255);
            $table->timestamp('created_at')->nullable();

            $table->index('claim_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_claim_images');
        Schema::dropIfExists('warranty_claims');
    }
};
