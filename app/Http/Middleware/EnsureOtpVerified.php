<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $verifiedPhone = session('order_phone_verified');
        $requestedPhone = $request->input('client_phone') ?? $request->input('phone');

        if (! $verifiedPhone || ! $requestedPhone || $verifiedPhone !== $requestedPhone) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unverified mobile dispatch credentials. Please verify SMS OTP prior to commissioning order.',
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('checkout.index')
                ->with('error', 'Please authenticate your patron mobile number via OTP before proceeding to payment.');
        }

        return $next($request);
    }
}
