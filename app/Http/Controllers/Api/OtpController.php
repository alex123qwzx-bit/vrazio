<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtpSendRequest;
use App\Http\Requests\OtpVerifyRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Dispatch SMS OTP to patron's mobile.
     */
    public function send(OtpSendRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $result = $this->otpService->sendOtp($phone);

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    /**
     * Verify patron OTP and establish checkout session credential.
     */
    public function verify(OtpVerifyRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $otp = $request->validated('otp');

        $result = $this->otpService->verifyOtp($phone, $otp);

        if ($result['success']) {
            session(['order_phone_verified' => $phone]);
            return response()->json([
                'success' => true,
                'message' => 'Mobile number successfully verified for transit protocol.',
                'phone' => $phone,
            ]);
        }

        return response()->json($result, 422);
    }
}
