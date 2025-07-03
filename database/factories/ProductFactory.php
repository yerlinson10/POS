<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'sku'              => $this->faker->unique()->bothify('PROD-####'),
            'name'             => $this->faker->unique()->word(),
            'description'      => $this->faker->optional()->paragraph(),
            'category_id'      => \App\Models\Category::all()->random()->id,
            'unit_measure_id'  => \App\Models\UnitMeasure::all()->random()->id,
            'price'            => $this->faker->randomFloat(2, 1, 500),
            'stock'            => $this->faker->numberBetween(0, 100),
        ];
    }
}
