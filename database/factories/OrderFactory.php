<?php

namespace Database\Factories;

use App\Enums\ClientType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\TelegramClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'telegram_client_id' => TelegramClient::factory(),
            'status' => OrderStatus::New,
            'price_type' => ClientType::Consumer,
            'total' => fake()->numberBetween(100, 5000) * 1000,
            'delivery_latitude' => fake()->latitude(41.2, 41.4),
            'delivery_longitude' => fake()->longitude(69.1, 69.4),
            'delivery_address' => fake()->streetAddress(),
            'comment' => null,
        ];
    }
}
