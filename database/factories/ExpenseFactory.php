<?php

namespace Database\Factories;

use App\Enums\ExpenseCategory;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => ExpenseCategory::Packaging,
            'amount' => fake()->numberBetween(10, 500) * 1000,
            'spent_on' => today(),
            'note' => fake()->sentence(3),
        ];
    }
}
