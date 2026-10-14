<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class WebAppOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'number' => $this->number(),
            'status' => $this->status->value,
            'price_type' => $this->price_type->value,
            'unit' => $this->unit()->value,
            'total' => $this->total,
            'comment' => $this->comment,
            'delivery' => [
                'latitude' => $this->delivery_latitude,
                'longitude' => $this->delivery_longitude,
                'address' => $this->delivery_address,
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'items' => $this->items->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'weight_grams' => $item->weight_grams,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ]),
        ];
    }
}
