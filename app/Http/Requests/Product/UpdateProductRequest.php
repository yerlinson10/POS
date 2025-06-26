<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'name' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }
}
