<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item to refund is required.',
            'items.min' => 'At least one item to refund is required.',
            'items.*.sale_item_id.required' => 'Sale item ID is required for each refund item.',
            'items.*.quantity.required' => 'Quantity is required for each refund item.',
            'items.*.quantity.min' => 'Refund quantity must be at least 1.',
        ];
    }
}
