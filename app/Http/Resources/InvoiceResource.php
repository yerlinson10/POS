<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'id' => $this->id,
                'date' => $this->date->format('Y-m-d'),
                'customer_id' => $this->customer_id,
                'customer' => $this->customer ? [
                    'id' => $this->customer->id,
                    'name' => $this->customer->first_name . ' ' . $this->customer->last_name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'address' => $this->customer->address,
                ] : null,
                'subtotal_amount' => $this->subtotal,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'discount_amount' => $this->discount_amount,
                'tax_amount' => $this->tax_amount,
                'total_amount' => $this->total_amount,
                'paid_amount' => $this->paid_amount,
                'debt_amount' => $this->debt_amount,
                'status' => $this->status,
                'payment_status' => $this->payment_status,
                'payment_method' => $this->payment_method,
                'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,
                'items' => $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'sku' => $item->product->sku,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_amount' => $item->line_total,
                ]),
                'debt' => $this->debt ? [
                    'id' => $this->debt->id,
                    'original_amount' => $this->debt->original_amount,
                    'remaining_amount' => $this->debt->remaining_amount,
                    'paid_amount' => $this->debt->paid_amount,
                    'status' => $this->debt->status,
                    'debt_date' => $this->debt->debt_date,
                    'due_date' => $this->debt->due_date,
                ] : null,
                'payments' => $this->payments->map(fn($payment) => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date,
                    'description' => $payment->description,
                ]),
                'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            ];
    }
}
