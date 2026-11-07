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
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number', 100)->nullable()->after('fulfillment_status');
            }
            if (! Schema::hasColumn('orders', 'tracking_courier')) {
                $table->string('tracking_courier', 100)->nullable()->after('tracking_number');
            }
            if (! Schema::hasColumn('orders', 'tracking_notes')) {
                $table->text('tracking_notes')->nullable()->after('tracking_courier');
            }
            if (! Schema::hasColumn('orders', 'packed_at')) {
                $table->timestamp('packed_at')->nullable()->after('tracking_notes');
            }
            if (! Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('packed_at');
            }
            if (! Schema::hasColumn('orders', 'out_for_delivery_at')) {
                $table->timestamp('out_for_delivery_at')->nullable()->after('shipped_at');
            }
            if (! Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('out_for_delivery_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_number',
                'tracking_courier',
                'tracking_notes',
                'packed_at',
                'shipped_at',
                'out_for_delivery_at',
                'delivered_at',
            ]);
        });
    }
};
