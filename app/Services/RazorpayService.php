<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class RazorpayService
{
    private ?Api $api = null;
    private bool $isSimulated = true;

    public function __construct()
    {
        $key = (string) config('services.razorpay.key', '');
        $secret = (string) config('services.razorpay.secret', '');

        // Determine if credentials are valid production/test keys or placeholders
        $isPlaceholder = empty($key)
            || empty($secret)
            || str_contains($key, 'xxxx')
            || str_contains($secret, 'xxxx')
            || str_contains($key, 'placeholder')
            || str_contains($secret, 'placeholder');

        if (class_exists(Api::class) && ! $isPlaceholder) {
            try {
                $this->api = new Api($key, $secret);
                $this->isSimulated = false;
            } catch (\Throwable $e) {
                Log::warning('Razorpay Api initialization failed: ' . $e->getMessage());
                $this->api = null;
                $this->isSimulated = true;
            }
        } else {
            $this->isSimulated = true;
        }
    }

    /**
     * Whether Razorpay is operating in mock / simulation mode.
     */
    public function isSimulated(): bool
    {
        return $this->isSimulated;
    }

    /**
     * Create a Razorpay order for payment processing.
     *
     * @param  string  $receipt   Internal order reference
     * @param  float   $amount    Total amount in INR (rupees, not paise)
     * @param  string  $currency  ISO currency code
     */
    public function createOrder(string $receipt, float $amount, string $currency = 'INR'): object
    {
        if ($this->api && ! $this->isSimulated) {
            try {
                return $this->api->order->create([
                    'receipt' => $receipt,
                    'amount' => (int) round($amount * 100), // Convert to paise
                    'currency' => $currency,
                    'payment_capture' => 1,
                ]);
            } catch (\Throwable $e) {
                Log::error('Razorpay Order API call failed: ' . $e->getMessage(), [
                    'error' => $e->getMessage(),
                    'receipt' => $receipt,
                ]);

                if (! app()->environment('testing')) {
                    throw $e;
                }
            }
        }

        if (! app()->environment('testing')) {
            throw new \RuntimeException('Razorpay payment gateway is not initialized with live credentials.');
        }

        // Seamless fallback simulation ONLY for automated unit/feature tests
        return (object) [
            'id' => 'order_sim_' . bin2hex(random_bytes(8)),
            'receipt' => $receipt,
            'amount' => (int) round($amount * 100),
            'currency' => $currency,
            'status' => 'created',
        ];
    }

    /**
     * Verify the Razorpay payment signature to confirm authenticity.
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        // Only allow simulated verification in automated testing environment
        if (str_starts_with($orderId, 'order_sim_')) {
            return app()->environment('testing') && str_starts_with($signature, 'sig_sim_');
        }

        if ($this->api && ! $this->isSimulated) {
            try {
                $this->api->utility->verifyPaymentSignature([
                    'razorpay_order_id' => $orderId,
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                ]);

                return true;
            } catch (\Throwable $e) {
                Log::warning('Razorpay signature verification failed: ' . $e->getMessage(), [
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                ]);

                return false;
            }
        }

        return false;
    }

    /**
     * Verify a Razorpay webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature, ?string $secret = null): bool
    {
        $secret = $secret ?? (string) config('services.razorpay.webhook_secret', '');
        if (empty($secret) || empty($signature)) {
            return false;
        }

        try {
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            return hash_equals($expectedSignature, $signature);
        } catch (\Throwable $e) {
            Log::warning('Razorpay webhook signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a refund through Razorpay API.
     *
     * @param  string  $paymentId  Razorpay payment ID (e.g. pay_xxx)
     * @param  float   $amount     Amount in INR
     * @param  array   $notes      Optional metadata notes
     * @return object  Razorpay Refund object or simulated refund object
     */
    public function createRefund(string $paymentId, float $amount, array $notes = []): object
    {
        $amountInPaise = (int) round($amount * 100);

        if ($this->api && ! $this->isSimulated) {
            try {
                return $this->api->payment->fetch($paymentId)->refund([
                    'amount' => $amountInPaise,
                    'notes' => $notes,
                ]);
            } catch (\Throwable $e) {
                Log::error('Razorpay Refund API call failed: ' . $e->getMessage(), [
                    'payment_id' => $paymentId,
                    'amount' => $amount,
                ]);
                throw $e;
            }
        }

        // Simulated refund response for continuity, tests, and mock mode
        return (object) [
            'id' => 'rfnd_sim_' . bin2hex(random_bytes(8)),
            'payment_id' => $paymentId,
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'status' => 'processed',
            'notes' => $notes,
            'created_at' => time(),
        ];
    }

    /**
     * Get the Razorpay publishable key for frontend integration.
     */
    public function getKey(): string
    {
        $key = (string) config('services.razorpay.key', '');
        if (empty($key) || str_contains($key, 'xxxx') || str_contains($key, 'placeholder')) {
            return 'rzp_test_simulated_key';
        }
        return $key;
    }
}
