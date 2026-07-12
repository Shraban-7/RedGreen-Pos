<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', Rule::in(['percentage', 'flat'])],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.vat_percentage' => ['nullable', 'numeric', 'min:0'],
            
            'discount_type' => ['nullable', Rule::in(['percentage', 'flat'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'vat_percentage' => ['nullable', 'numeric', 'min:0'],
            
            'payment_method' => ['required', Rule::in(['cash', 'card', 'mobile_banking', 'bank_transfer'])],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.required' => 'Product ID is required for each item.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}
