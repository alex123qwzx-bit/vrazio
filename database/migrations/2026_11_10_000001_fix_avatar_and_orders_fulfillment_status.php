<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Disable wrapping in transaction for PgBouncer / Neon pooler compatibility.
     *
     * @var bool
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1. Expand users.avatar to TEXT so long OAuth URLs (Google, etc.) don't truncate
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN avatar TYPE TEXT');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY avatar TEXT NULL');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->text('avatar')->nullable()->change();
            });
        }

        // 2. Drop PostgreSQL check constraint on orders.fulfillment_status and convert to flexible VARCHAR(50)
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_fulfillment_status_check');
            DB::statement('ALTER TABLE orders ALTER COLUMN fulfillment_status TYPE VARCHAR(50)');
            DB::statement("ALTER TABLE orders ALTER COLUMN fulfillment_status SET DEFAULT 'order_placed'");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY fulfillment_status VARCHAR(50) NOT NULL DEFAULT 'order_placed'");
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('fulfillment_status', 50)->default('order_placed')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN avatar TYPE VARCHAR(255)');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY avatar VARCHAR(255) NULL');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar', 255)->nullable()->change();
            });
        }
    }
};
