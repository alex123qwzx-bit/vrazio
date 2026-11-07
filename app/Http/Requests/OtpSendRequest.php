<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtpSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'A valid patron mobile number is required.',
            'phone.regex' => 'Please provide a legitimate 10-digit Indian mobile number (beginning with 6, 7, 8, or 9).',
        ];
    }
}
