<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $productId = $this->route('product');

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'user_id' => ['nullable', 'exists:users,id'],

            'name' => ['required', 'string', 'max:255'],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($productId),
            ],

            'sku' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => ['nullable', 'string'],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'buying_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],

            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0'],

            'stock_in' => ['nullable', 'integer', 'min:0'],
            'stock_out' => ['nullable', 'integer', 'min:0'],
            'low_stock_quantity' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required.',
            'brand_id.required' => 'Brand is required.',
            'user_id.required' => 'User is required.',
            'slug.unique' => 'This product slug already exists.',
            'sku.unique' => 'This SKU already exists.',
            'thumbnail.image' => 'Thumbnail must be a valid image.',
        ];
    }
}
