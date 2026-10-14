<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\TelegramClient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class CatalogProductResource extends JsonResource
{
    /**
     * Prices are only exposed to registered, active clients — consumer or reseller price depending on their type.
     * Consumers are priced per piece, resellers per package (with the consumer package price shown for comparison).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TelegramClient|null $client */
        $client = $request->attributes->get('telegram_client');
        $canSeePrices = (bool) $client?->isActive();

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'weight' => $this->weightLabel(),
            'weight_grams' => $this->weight_grams,
            'box_quantity' => $this->box_quantity,
            'image_url' => $this->image_url,
            'illustration' => $this->illustration?->value,
            'color' => $this->category->color,
            'is_new' => $this->is_new,
            'is_featured' => $this->is_featured,
            'price' => $this->when($canSeePrices, fn (): ?int => $this->priceFor($client->type)),
            'unit' => $this->when($canSeePrices, fn (): string => $client->type->orderUnit()->value),
            'consumer_price' => $this->when($canSeePrices && $client->isReseller(), fn (): ?int => $this->consumerPackagePrice()),
            'orderable' => $this->when($canSeePrices, fn (): bool => $this->isOrderableBy($client->type)),
            'min_quantity' => $this->when($canSeePrices, fn (): int => $this->minQuantityFor($client->type)),
            'quantity_step' => $this->when($canSeePrices, fn (): int => $this->quantityStepFor($client->type)),
        ];
    }
}
