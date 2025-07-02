<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'  => \App\Models\Customer::factory(),
            'user_id'  => \App\Models\User::factory(), // o User::factory() si lo deseas
            'date'         => $this->faker->date(),
            'subtotal'         => $this->faker->randomFloat(2, 10, 1000), // Genera un subtotal entre 10 y 1000
            'discount_type' => $this->faker->randomElement(['percentage', 'fixed', null]),
            'discount_value' => $this->faker->randomFloat(2, 0, 100), // Genera un valor de descuento entre 0 y 100
            'total_amount' => 0,    // se actualizará tras crear ítems
            'status'       => $this->faker->randomElement(['pendiente','pagado','anulado']),
        ];
    }
}
