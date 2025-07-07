<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosSession>
 */
class PosSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openedAt = fake()->dateTimeBetween('-30 days', 'now');
        $isClosed = fake()->boolean(70); // 70% chance of being closed

        return [
            'user_id' => \App\Models\User::factory(),
            'opened_at' => $openedAt,
            'closed_at' => $isClosed ? fake()->dateTimeBetween($openedAt, 'now') : null,
            'initial_cash' => fake()->randomFloat(2, 0, 1000),
            'final_cash' => $isClosed ? fake()->randomFloat(2, 0, 1500) : null,
            'expected_cash' => $isClosed ? fake()->randomFloat(2, 0, 1500) : null,
            'cash_difference' => $isClosed ? fake()->randomFloat(2, -50, 50) : null,
            'opening_notes' => fake()->optional()->sentence(),
            'closing_notes' => $isClosed ? fake()->optional()->sentence() : null,
            'status' => $isClosed ? 'closed' : 'open',
            'cash_breakdown' => $this->generateCashBreakdown(),
        ];
    }

    /**
     * Generate a sample cash breakdown
     */
    private function generateCashBreakdown(): array
    {
        return [
            'bills' => [
                ['denomination' => 100, 'quantity' => fake()->numberBetween(0, 10)],
                ['denomination' => 50, 'quantity' => fake()->numberBetween(0, 20)],
                ['denomination' => 20, 'quantity' => fake()->numberBetween(0, 30)],
                ['denomination' => 10, 'quantity' => fake()->numberBetween(0, 40)],
                ['denomination' => 5, 'quantity' => fake()->numberBetween(0, 50)],
                ['denomination' => 1, 'quantity' => fake()->numberBetween(0, 100)],
            ],
            'coins' => [
                ['denomination' => 0.5, 'quantity' => fake()->numberBetween(0, 20)],
                ['denomination' => 0.25, 'quantity' => fake()->numberBetween(0, 40)],
                ['denomination' => 0.10, 'quantity' => fake()->numberBetween(0, 100)],
                ['denomination' => 0.05, 'quantity' => fake()->numberBetween(0, 200)],
                ['denomination' => 0.01, 'quantity' => fake()->numberBetween(0, 500)],
            ]
        ];
    }

    /**
     * Indicate that the session is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'closed_at' => null,
            'final_cash' => null,
            'expected_cash' => null,
            'cash_difference' => null,
            'closing_notes' => null,
            'status' => 'open',
        ]);
    }

    /**
     * Indicate that the session is closed.
     */
    public function closed(): static
    {
        return $this->state(function (array $attributes) {
            $openedAt = $attributes['opened_at'];
            $closedAt = fake()->dateTimeBetween($openedAt, 'now');
            $expectedCash = $attributes['initial_cash'] + fake()->randomFloat(2, 0, 500);
            $finalCash = $expectedCash + fake()->randomFloat(2, -50, 50);

            return [
                'closed_at' => $closedAt,
                'final_cash' => $finalCash,
                'expected_cash' => $expectedCash,
                'cash_difference' => $finalCash - $expectedCash,
                'closing_notes' => fake()->optional()->sentence(),
                'status' => 'closed',
            ];
        });
    }
}
