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
            if (! Schema::hasColumn('orders', 'estimated_delivery_at')) {
                $table->timestamp('estimated_delivery_at')->nullable()->after('delivered_at');
            }
            if (! Schema::hasColumn('orders', 'delivery_time_slot')) {
                $table->string('delivery_time_slot', 100)->nullable()->after('estimated_delivery_at');
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
                'estimated_delivery_at',
                'delivery_time_slot',
            ]);
        });
    }
};
