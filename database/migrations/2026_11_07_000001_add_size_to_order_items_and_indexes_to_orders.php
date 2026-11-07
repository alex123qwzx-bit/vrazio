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
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'size')) {
                $table->string('size', 50)->nullable()->after('quantity');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('client_phone');
            $table->index('client_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'size')) {
                $table->dropColumn('size');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['client_phone']);
            $table->dropIndex(['client_email']);
        });
    }
};
