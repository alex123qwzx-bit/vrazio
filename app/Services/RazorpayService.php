<?php

declare(strict_types=1);

namespace App\Services;

use Razorpay\Api\Api;

class RazorpayService
{
    private ?Api $api = null;

    public function __construct()
    {
        $key = config('services.razorpay.key', 'rzp_test_placeholder');
        $secret = config('services.razorpay.secret', 'placeholder_secret');

        if (class_exists(Api::class) && !empty($key) && !str_starts_with((string)$key, 'rzp_test_xxxx')) {
            try {
                $this->api = new Api($key, $secret);
            } catch (\Throwable) {
                $this->api = null;
            }
        }
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
        if ($this->api) {
            return $this->api->order->create([
                'receipt' => $receipt,
                'amount' => (int) round($amount * 100), // Convert to paise
                'currency' => $currency,
                'payment_capture' => 1,
            ]);
        }

        // Mock simulation in development / test environments
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
        if ($this->api) {
            try {
                $this->api->utility->verifyPaymentSignature([
                    'razorpay_order_id' => $orderId,
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                ]);

                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        // In mock simulation, accept simulated signature
        if (str_starts_with($orderId, 'order_sim_') || config('app.debug')) {
            return true;
        }

        return false;
    }

    /**
     * Get the Razorpay publishable key for frontend integration.
     */
    public function getKey(): string
    {
        return (string) config('services.razorpay.key', 'rzp_test_placeholder');
    }
}
