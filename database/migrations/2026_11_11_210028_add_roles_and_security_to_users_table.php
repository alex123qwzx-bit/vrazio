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
        // 1. Update PostgreSQL check constraint for roles if running on PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('patron', 'admin', 'super_admin', 'sub_manager'))");
        }

        // 2. Add columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_approved')) {
                $table->boolean('is_approved')->default(true)->after('role');
            }
            if (!Schema::hasColumn('users', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('is_approved');
            }
            if (!Schema::hasColumn('users', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'admin_pin')) {
                $table->string('admin_pin')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'password_confirmation_token')) {
                $table->string('password_confirmation_token')->nullable()->after('admin_pin');
            }
            if (!Schema::hasColumn('users', 'password_confirmation_expires_at')) {
                $table->timestamp('password_confirmation_expires_at')->nullable()->after('password_confirmation_token');
            }
        });

        // 3. Ensure existing admins are set to super_admin or admin with is_approved = true
        DB::table('users')
            ->whereIn('role', ['admin', 'super_admin'])
            ->update([
                'is_approved' => true,
                'approved_at' => now(),
            ]);

        // Elevate the primary admin (e.g. immortalpanday1@gmail.com, alex123qwzx@gmail.com, or ID 1/first admin) to super_admin
        $primaryAdmin = DB::table('users')->where('email', 'immortalpanday1@gmail.com')->first()
            ?? DB::table('users')->where('role', 'admin')->orderBy('id')->first();

        if ($primaryAdmin) {
            DB::table('users')->where('id', $primaryAdmin->id)->update([
                'role' => 'super_admin',
                'is_approved' => true,
                'approved_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'is_approved',
                'approved_at',
                'admin_pin',
                'password_confirmation_token',
                'password_confirmation_expires_at',
            ]);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('patron', 'admin'))");
        }
    }
};
