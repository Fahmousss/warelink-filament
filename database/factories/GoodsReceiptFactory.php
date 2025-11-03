<?php

namespace Database\Factories;

use App\Enums\GoodsReceiptStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoodReceipt>
 */
class GoodsReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'delivery_order_number' => fake()->unique()->bothify('DO-########'),
            'receipt_date' => now(),
            'status' => GoodsReceiptStatus::PENDING,
            'pod_scan_path' => null,
            'notes' => null,
        ];
    }
}
