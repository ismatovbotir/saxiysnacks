<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Jobs\SendTelegramMessage;
use App\Jobs\SyncOrderAdminMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\TelegramClient;
use App\Services\Telegram\ClientMessages;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    public function __construct(private ClientMessages $messages) {}

    /**
     * Create an order priced from the current catalog at the client's price type; client-sent prices are never trusted.
     *
     * Resellers always receive at their saved location. Anyone else must pass a delivery location,
     * which also becomes their saved location for next time.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $lines
     * @param  array{latitude: float, longitude: float, address?: string|null}|null  $location
     */
    public function handle(TelegramClient $client, array $lines, ?array $location = null, ?string $comment = null): Order
    {
        $usesSavedLocation = $client->isReseller() && $client->hasLocation();

        if (! $usesSavedLocation && $location === null) {
            throw ValidationException::withMessages(['latitude' => 'Please choose a delivery location.']);
        }

        $quantities = collect($lines)
            ->groupBy('product_id')
            ->map(fn ($group): int => (int) $group->sum('quantity'));

        $products = Product::query()
            ->active()
            ->whereKey($quantities->keys())
            ->get()
            ->filter(fn (Product $product): bool => $product->isOrderableBy($client->type))
            ->keyBy('id');

        if ($products->count() !== $quantities->count()) {
            throw ValidationException::withMessages([
                'items' => 'Some products are no longer available. Please refresh the catalog.',
            ]);
        }

        $order = DB::transaction(function () use ($client, $quantities, $products, $comment, $location, $usesSavedLocation): Order {
            if (! $usesSavedLocation) {
                $client->moveTo((float) $location['latitude'], (float) $location['longitude'], $location['address'] ?? null);
            }

            $items = $quantities->map(fn (int $quantity, int $productId): array => [
                'product_id' => $productId,
                'product_name' => $products[$productId]->translate('name', 'ru'),
                'weight_grams' => $products[$productId]->weight_grams,
                'box_quantity' => $products[$productId]->box_quantity,
                'quantity' => $quantity,
                'unit_price' => $products[$productId]->priceFor($client->type),
                'line_total' => $products[$productId]->priceFor($client->type) * $quantity,
            ])->values();

            $order = $client->orders()->create([
                'status' => OrderStatus::New,
                'price_type' => $client->type,
                'total' => $items->sum('line_total'),
                'delivery_latitude' => $client->latitude,
                'delivery_longitude' => $client->longitude,
                'delivery_address' => $location['address'] ?? $client->address,
                'comment' => $comment,
            ]);

            $order->items()->createMany($items->all());

            return $order;
        });

        SyncOrderAdminMessage::dispatch($order);
        SendTelegramMessage::dispatch($client->telegram_id, $this->messages->orderReceived($order, $client));

        return $order;
    }
}
