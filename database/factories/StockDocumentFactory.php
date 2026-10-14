<?php

namespace Database\Factories;

use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use App\Models\StockDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockDocument>
 */
class StockDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => StockDocumentType::Receipt,
            'number' => 'IN-'.fake()->unique()->numerify('#####'),
            'document_date' => today(),
            'status' => StockDocumentStatus::Draft,
            'counterparty' => fake()->company(),
            'total' => 0,
        ];
    }
}
