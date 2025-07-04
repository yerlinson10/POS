<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invoice = \App\Models\Invoice::inRandomOrder()->first();
        $product = \App\Models\Product::inRandomOrder()->first();
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $product->price;

        // Obtener descuento y tipo de descuento del invoice
        $discount = $invoice->discount_value ?? 0;
        $discountType = $invoice->discount_type ?? 'fixed'; // 'percent' o 'amount'

        if ($discountType === 'percentage') {
            $finalUnitPrice = max(0, $unitPrice - ($unitPrice * ($discount / 100)));
        } else {
            $finalUnitPrice = max(0, $unitPrice - $discount);
        }

        $lineTotal = round($finalUnitPrice * $quantity, 2);

        // Actualizar el total del invoice sumando el nuevo item
        $invoice->total_amount = round(($invoice->total_amount ?? 0) + $lineTotal, 2);
        $invoice->save();

        return [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }
}
