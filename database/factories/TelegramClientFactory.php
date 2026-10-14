<?php

namespace Database\Factories;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Models\TelegramClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TelegramClient>
 */
class TelegramClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'telegram_id' => fake()->unique()->numberBetween(100000000, 999999999),
            'telegram_username' => fake()->userName(),
            'language_code' => 'ru',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => '+99890'.fake()->numerify('#######'),
            'company_name' => fake()->company(),
            'vat_number' => fake()->numerify('#########'),
            'latitude' => fake()->latitude(41.2, 41.4),
            'longitude' => fake()->longitude(69.1, 69.4),
            'address' => fake()->streetAddress(),
            'location_updated_at' => now(),
            'type' => ClientType::Consumer,
            'status' => ClientStatus::Active,
        ];
    }

    public function reseller(): static
    {
        return $this->state(fn (array $attributes) => ['type' => ClientType::Reseller]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ClientStatus::Blocked]);
    }
}
