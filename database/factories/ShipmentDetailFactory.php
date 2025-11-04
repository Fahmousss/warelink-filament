<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShipmentDetail>
 */
class ShipmentDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory()->for(\App\Models\Supplier::factory(), 'supplier'),
            'quantity_shipped' => fake()->numberBetween(1, 99),
            'notes' => fake()->paragraph(),
        ];
    }
}
