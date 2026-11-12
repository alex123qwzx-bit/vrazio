<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PhoneOtp;
use Illuminate\Support\Facades\Http;

class OtpService
{
    /**
     * Normalize Indian phone numbers to 10 digits.
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) > 10 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        return substr($digits, -10);
    }

    /**
     * Send a new OTP to the given phone number.
     *
     * @return array{success: bool, message: string, dev_otp?: string}
     */
    public function sendOtp(string $phone): array
    {
        $phone = $this->normalizePhone($phone);
        if (strlen($phone) !== 10) {
            return ['success' => false, 'message' => 'Please enter a valid 10-digit mobile number'];
        }
        // Generate a cryptographically random 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // 3. Store with SHA-256 hash (never store plaintext)
        PhoneOtp::updateOrCreate(
            ['phone' => $phone],
            [
                'otp_hash' => hash('sha256', $otp),
                'expires_at' => now()->addMinutes(config('services.otp.expiry_minutes', 5)),
                'attempts' => 0,
                'verified_at' => null,
            ]
        );

        // 4. In dev mode, return the OTP in the response for testing
        if (config('services.otp.dev_mode')) {
            return [
                'success' => true,
                'message' => "OTP sent to +91 {$phone}",
                'dev_otp' => $otp,
            ];
        }

        // 5. Send via Fast2SMS in production (with SSL verification enabled)
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'authorization' => config('services.fast2sms.key'),
                ])->post('https://www.fast2sms.com/dev/bulkV2', [
                    'route' => config('services.fast2sms.route', 'otp'),
                    'variables_values' => $otp,
                    'numbers' => $phone,
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['return'] ?? false) === true) {
                return ['success' => true, 'message' => "OTP sent via SMS to +91 {$phone}"];
            }

            $statusCode = $data['status_code'] ?? null;
            $errorMsg = is_array($data['message'] ?? null)
                ? implode(', ', $data['message'])
                : ($data['message'] ?? 'Failed to send SMS. Please try again.');

            // If Fast2SMS requires 1-time website verification (code 996) or wallet balance (code 999)
            if ($statusCode === 996 || $statusCode === 999 || empty(config('services.fast2sms.key'))) {
                \Log::warning('Fast2SMS pending website domain verification or balance.', ['status_code' => $statusCode, 'data' => $data]);
                
                if (config('services.otp.dev_mode')) {
                    return [
                        'success' => true,
                        'message' => "Fast2SMS OTP pending website verification (Dev Mode). Test code: {$otp}",
                        'dev_otp' => $otp,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'SMS service is temporarily unavailable. Fast2SMS website verification is required. Please sign in via Google or Email.',
                ];
            }

            return ['success' => false, 'message' => $errorMsg];
        } catch (\Throwable $e) {
            \Log::error('Fast2SMS Gateway Error', ['error' => $e->getMessage()]);
            if (config('services.otp.dev_mode')) {
                return [
                    'success' => true,
                    'message' => "OTP generated for testing (Dev Mode): {$otp}",
                    'dev_otp' => $otp,
                ];
            }
            return ['success' => false, 'message' => config('app.debug') ? ('SMS Gateway error: ' . $e->getMessage()) : 'We made a mistake reaching our SMS gateway. Please try again shortly.'];
        }
    }

    /**
     * Verify the OTP entered by the user against the stored hash.
     *
     * @return array{success: bool, message: string}
     */
    public function verifyOtp(string $phone, string $inputOtp): array
    {
        $phone = $this->normalizePhone($phone);
        $inputOtp = trim($inputOtp);

        $record = PhoneOtp::where('phone', $phone)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if (!$record) {
            return ['success' => false, 'message' => 'OTP expired or not found. Please request a new one.'];
        }

        if ($record->attempts >= 5) {
            $record->delete();
            return ['success' => false, 'message' => 'Too many failed attempts. This OTP has expired. Please request a new one.'];
        }

        if (!hash_equals($record->otp_hash, hash('sha256', $inputOtp))) {
            $record->incrementAttempts();
            if ($record->attempts >= 5) {
                $record->delete();
                return ['success' => false, 'message' => 'Too many failed attempts. This OTP has expired. Please request a new one.'];
            }
            return ['success' => false, 'message' => 'Invalid OTP. Please try again.'];
        }

        $record->update(['verified_at' => now()]);

        return ['success' => true, 'message' => 'Phone verified successfully'];
    }
}
