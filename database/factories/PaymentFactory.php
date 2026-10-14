<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'telegram_client_id' => null,
            'amount' => fake()->numberBetween(10, 500) * 1000,
            'method' => PaymentMethod::Cash,
            'paid_on' => today(),
            'note' => null,
        ];
    }
}
