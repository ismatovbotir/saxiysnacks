<?php

namespace Database\Factories;

use App\Enums\ProductIllustration;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ['uz' => $name, 'ru' => $name, 'en' => $name],
            'description' => null,
            'weight_grams' => fake()->randomElement([30, 47, 60, 80]),
            'box_quantity' => $boxQuantity = fake()->randomElement([30, 40]),
            'consumer_price' => $piecePrice = fake()->numberBetween(2, 15) * 500,
            'reseller_price' => (int) ($piecePrice * $boxQuantity * 0.8),
            'illustration' => fake()->randomElement(ProductIllustration::cases()),
            'is_featured' => false,
            'is_new' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function withoutPrices(): static
    {
        return $this->state(fn (array $attributes) => ['consumer_price' => null, 'reseller_price' => null]);
    }
}
