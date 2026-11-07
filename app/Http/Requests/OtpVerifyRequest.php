<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtpVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'otp' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Mobile number is required for OTP reconciliation.',
            'phone.regex' => 'The mobile format must be a 10-digit Indian number.',
            'otp.required' => 'The 6-digit authentication cipher is required.',
            'otp.digits' => 'The verification cipher must be exactly 6 digits.',
        ];
    }
}
