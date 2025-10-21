<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_date' => fake()->date(),
            'expected_delivery_date' => fake()->date(),
            'status' => PurchaseOrderStatus::PENDING,
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'notes' => fake()->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
