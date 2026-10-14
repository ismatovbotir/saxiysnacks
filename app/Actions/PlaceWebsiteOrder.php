<?php

namespace App\Actions;

use App\Enums\ClientType;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Jobs\SyncOrderAdminMessage;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceWebsiteOrder
{
    /**
     * Create a guest order from the website cart. Guests are consumers, so they order pieces at consumer prices;
     * products without a price are still accepted at 0 and the manager confirms the total when calling back.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $lines
     */
    public function handle(string $name, string $phone, array $lines, ?string $comment = null): Order
    {
        $type = ClientType::Consumer;

        $quantities = collect($lines)
            ->groupBy('product_id')
            ->map(fn ($group): int => (int) $group->sum('quantity'));

        $products = Product::query()->active()->whereKey($quantities->keys())->get()->keyBy('id');

        if ($products->count() !== $quantities->count()) {
            throw ValidationException::withMessages([
                'items' => __('site.orderUnavailable'),
            ]);
        }

        $invalid = $quantities->reject(fn (int $quantity, int $productId): bool => $products[$productId]->allowsQuantity($type, $quantity));

        if ($invalid->isNotEmpty()) {
            $product = $products[$invalid->keys()->first()];

            throw ValidationException::withMessages([
                'items' => __('site.orderRules', [
                    'name' => $product->translate('name'),
                    'min' => $product->minQuantityFor($type),
                    'step' => $product->quantityStepFor($type),
                ]),
            ]);
        }

        $order = DB::transaction(function () use ($name, $phone, $comment, $quantities, $products, $type): Order {
            $items = $quantities->map(fn (int $quantity, int $productId): array => [
                'product_id' => $productId,
                'product_name' => $products[$productId]->translate('name', 'ru'),
                'weight_grams' => $products[$productId]->weight_grams,
                'box_quantity' => $products[$productId]->box_quantity,
                'quantity' => $quantity,
                'unit_price' => $products[$productId]->priceFor($type) ?? 0,
                'line_total' => ($products[$productId]->priceFor($type) ?? 0) * $quantity,
            ])->values();

            $order = Order::create([
                'source' => OrderSource::Website,
                'customer_name' => $name,
                'customer_phone' => $phone,
                'status' => OrderStatus::New,
                'price_type' => $type,
                'total' => $items->sum('line_total'),
                'comment' => $comment,
            ]);

            $order->items()->createMany($items->all());

            return $order;
        });

        SyncOrderAdminMessage::dispatch($order);

        return $order;
    }
}
