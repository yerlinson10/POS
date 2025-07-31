<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            'company_name' => 'required|string|max:150',
            'contact_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'current_debt' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'El nombre de la empresa es obligatorio.',
            'company_name.max' => 'El nombre de la empresa no puede exceder 150 caracteres.',
            'contact_name.max' => 'El nombre de contacto no puede exceder 100 caracteres.',
            'email.email' => 'Debe proporcionar un email válido.',
            'email.unique' => 'Este email ya está registrado para otro proveedor.',
            'phone.max' => 'El teléfono no puede exceder 20 caracteres.',
            'current_debt.numeric' => 'La deuda actual debe ser un número.',
            'current_debt.min' => 'La deuda actual no puede ser negativa.',
        ];
    }
}
