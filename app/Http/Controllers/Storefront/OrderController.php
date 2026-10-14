<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\PlaceWebsiteOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\PlaceWebsiteOrderRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Website cart checkout: a guest sends name, phone and pieces; a manager calls back to confirm.
 */
class OrderController extends Controller
{
    public function __invoke(PlaceWebsiteOrderRequest $request, PlaceWebsiteOrder $placeOrder): JsonResponse
    {
        $items = collect($request->validated('items'));
        $productIds = Product::query()->active()->whereIn('slug', $items->pluck('slug'))->pluck('id', 'slug');

        if ($productIds->count() !== $items->count()) {
            throw ValidationException::withMessages(['items' => __('site.orderUnavailable')]);
        }

        $order = $placeOrder->handle(
            $request->validated('name'),
            $request->validated('phone'),
            $items->map(fn (array $item): array => ['product_id' => $productIds[$item['slug']], 'quantity' => (int) $item['quantity']])->all(),
            $request->validated('comment'),
        );

        return response()->json(['number' => $order->number(), 'message' => __('site.orderSent', ['number' => $order->number()])], 201);
    }
}
