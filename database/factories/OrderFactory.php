<?php

namespace Database\Factories;

use App\Enums\ClientType;
use App\Enums\OrderSource;
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
            'source' => OrderSource::Telegram,
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

    /**
     * A guest order from the website cart: name and phone only, no Telegram customer or map location.
     */
    public function fromWebsite(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => OrderSource::Website,
            'telegram_client_id' => null,
            'customer_name' => fake()->name(),
            'customer_phone' => '+998 90 '.fake()->numerify('### ## ##'),
            'delivery_latitude' => null,
            'delivery_longitude' => null,
            'delivery_address' => null,
        ]);
    }
}
