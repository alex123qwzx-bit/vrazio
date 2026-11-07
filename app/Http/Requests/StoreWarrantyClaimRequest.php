<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\WarrantyClaim;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarrantyClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
                'unique:warranty_claims,order_id',
            ],
            'customer_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s\-]{10,15}$/'],
            'email' => ['required', 'email', 'max:150'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'issue_type' => ['required', 'string', Rule::in(array_keys(WarrantyClaim::ISSUE_TYPES))],
            'description' => ['required', 'string', 'min:15', 'max:3000'],
            'photos' => ['required', 'array', 'min:2', 'max:3'],
            'photos.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120', // 5MB max
            ],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_id.unique' => 'A warranty claim has already been submitted for this order.',
            'issue_type.required' => 'Please select the specific craftsmanship issue type.',
            'issue_type.in' => 'Please select a valid craftsmanship issue category.',
            'description.min' => 'Please provide a detailed description (at least 15 characters) of the issue.',
            'photos.required' => 'Please upload 2 to 3 photos of the piece showing the defect.',
            'photos.min' => 'You must upload at least 2 clear photos showing the issue.',
            'photos.max' => 'You can upload a maximum of 3 photos.',
            'photos.*.mimes' => 'Photos must be in JPG, PNG, or WebP format.',
            'photos.*.max' => 'Each photo must not exceed 5 MB in size.',
        ];
    }
}
