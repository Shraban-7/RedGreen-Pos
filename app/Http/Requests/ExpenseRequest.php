<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_category_id' => ['nullable', 'exists:expense_categories,id'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string'],
            'attachment' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Expense title is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than zero.',
            'expense_date.required' => 'Expense date is required.',
            'expense_date.before_or_equal' => 'Expense date cannot be in the future.',
            'expense_category_id.exists' => 'Selected category does not exist.',
            'attachment.image' => 'Attachment must be a valid image.',
        ];
    }
}