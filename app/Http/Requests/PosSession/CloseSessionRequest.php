<?php

namespace App\Http\Requests\PosSession;

use Illuminate\Foundation\Http\FormRequest;

class CloseSessionRequest extends FormRequest
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
            'final_cash' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'closing_notes' => ['nullable', 'string', 'max:1000'],
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
            'final_cash.required' => 'El monto final en efectivo es obligatorio.',
            'final_cash.numeric' => 'El monto final debe ser un número válido.',
            'final_cash.min' => 'El monto final no puede ser negativo.',
            'final_cash.max' => 'El monto final no puede exceder $999,999.99.',
            'closing_notes.max' => 'Las notas de cierre no pueden exceder 1000 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir el monto final a decimal si viene como string
        if ($this->has('final_cash') && is_string($this->final_cash)) {
            $this->merge([
                'final_cash' => (float) str_replace(',', '', $this->final_cash)
            ]);
        }
    }
}
