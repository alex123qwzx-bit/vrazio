<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:120'],
            'client_email' => ['required', 'string', 'email:rfc', 'max:255'],
            'client_phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'shipping_address' => ['required', 'array'],
            'shipping_address.street' => ['required', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:100'],
            'shipping_address.state' => ['required', 'string', 'max:100'],
            'shipping_address.pincode' => ['required', 'string', 'regex:/^\d{6}$/'],
            'shipping_method' => ['required', 'in:standard,express'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_name.required' => 'Patron name is required for insurance consignment.',
            'client_email.required' => 'A valid email address is required for dispatch certificates.',
            'client_phone.regex' => 'A verified 10-digit Indian mobile is required for armoured delivery OTP confirmation.',
            'shipping_address.pincode.regex' => 'The Postal Pincode must be a 6-digit Indian PIN code.',
            'shipping_method.in' => 'Selected transit tier is invalid.',
        ];
    }
}
