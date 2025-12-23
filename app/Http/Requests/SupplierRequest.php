<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier'); // model
        $supplierId = $supplier?->id;         // ID

        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplierId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('suppliers', 'phone')->ignore($supplierId),
                'regex:/^(\+8801[3-9]\d{8}|8801[3-9]\d{8}|01[3-9]\d{8}|1[3-9]\d{8})$/',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
