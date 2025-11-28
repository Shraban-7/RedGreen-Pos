<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'parent_id' => 'nullable|exists:categories,id',
            'name' => [
                'required',
                'string'
            ],
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required',
            'name.unique' => 'Category name must be unique',
            'parent_id.exists' => 'Parent category does not exist',
            'status.boolean' => 'Status must be true or false',
        ];
    }
}
