<?php

namespace App\Http\Requests\PosSession;

use Illuminate\Foundation\Http\FormRequest;

class OpenSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'initial_cash' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'opening_notes' => ['nullable', 'string', 'max:1000'],
            'cash_breakdown' => ['nullable', 'array'],
            'cash_breakdown.bills' => ['nullable', 'array'],
            'cash_breakdown.coins' => ['nullable', 'array'],
            'cash_breakdown.bills.*.denomination' => ['required_with:cash_breakdown.bills', 'numeric', 'min:0'],
            'cash_breakdown.bills.*.quantity' => ['required_with:cash_breakdown.bills', 'integer', 'min:0'],
            'cash_breakdown.coins.*.denomination' => ['required_with:cash_breakdown.coins', 'numeric', 'min:0'],
            'cash_breakdown.coins.*.quantity' => ['required_with:cash_breakdown.coins', 'integer', 'min:0'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'initial_cash.required' => 'El monto inicial en efectivo es obligatorio.',
            'initial_cash.numeric' => 'El monto inicial debe ser un número válido.',
            'initial_cash.min' => 'El monto inicial no puede ser negativo.',
            'initial_cash.max' => 'El monto inicial no puede exceder $999,999.99.',
            'opening_notes.max' => 'Las notas de apertura no pueden exceder 1000 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir el monto inicial a decimal si viene como string
        if ($this->has('initial_cash') && is_string($this->initial_cash)) {
            $this->merge([
                'initial_cash' => (float) str_replace(',', '', $this->initial_cash)
            ]);
        }
    }
}
