<?php

namespace Database\Factories;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'delivery_order_number' => fake()->sentence(),
            'shipping_date' => now(),
            'estimated_arrival_date' => now()->addDays(7),
            'status' => ShipmentStatus::DRAFT,
            'do_scan_path' => null,
            'notes' => null,
        ];
    }
}
