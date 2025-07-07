<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Models\Invoice;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Check if invoice exists and validate its status
        $invoiceId = $this->route('invoice');
        $invoice = Invoice::find($invoiceId);

        if ($invoice && in_array($invoice->status, ['paid', 'canceled'])) {
            throw ValidationException::withMessages([
                'invoice' => ["Cannot modify an invoice that has been {$invoice->status}. Only pending invoices can be edited."]
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.line_total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a valid date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: pending, paid, canceled.',
            'discount_type.in' => 'Discount type must be percentage or fixed.',
            'discount_value.numeric' => 'Discount value must be a number.',
            'discount_value.min' => 'Discount value must be at least 0.',
            'items.required' => 'At least one item is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.exists' => 'Selected product does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.numeric' => 'Quantity must be a number.',
            'items.*.quantity.min' => 'Quantity must be at least 0.01.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.numeric' => 'Unit price must be a number.',
            'items.*.unit_price.min' => 'Unit price must be at least 0.',
        ];
    }
}
