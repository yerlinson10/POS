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
            'total_amount' => 0,    // se actualizará tras crear ítems
            'status'       => $this->faker->randomElement(['pendiente','pagado','anulado']),
        ];
    }
}
