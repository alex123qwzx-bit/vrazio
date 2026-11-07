<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PhoneOtp;
use Illuminate\Console\Command;

class CleanupExpiredOtps extends Command
{
    protected $signature = 'otp:cleanup';

    protected $description = 'Purge expired and stale OTP records from database table';

    public function handle(): int
    {
        $deleted = PhoneOtp::where('expires_at', '<', now()->subHours(12))
            ->orWhere(function ($query) {
                $query->whereNotNull('verified_at')
                    ->where('created_at', '<', now()->subHours(24));
            })
            ->delete();

        $this->info("Purged {$deleted} stale OTP records from atelier security log.");

        return Command::SUCCESS;
    }
}
