<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TelegramClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The customer's "My list" in the Mini App: products saved for quick re-ordering.
 */
class SavedProductController extends Controller
{
    /**
     * Add products to the list, or update their usual quantity.
     */
    public function store(Request $request): JsonResponse
    {
        $client = $this->client($request);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->where('is_active', true)],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $client->savedProducts()->syncWithoutDetaching(
            collect($validated['items'])->mapWithKeys(fn (array $item): array => [$item['product_id'] => ['quantity' => $item['quantity']]])->all(),
        );

        return response()->json(['saved' => $client->savedQuantities()]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $client = $this->client($request);

        $client->savedProducts()->detach($product);

        return response()->json(['saved' => $client->savedQuantities()]);
    }

    private function client(Request $request): TelegramClient
    {
        $client = $request->attributes->get('telegram_client');

        abort_unless($client?->isActive(), 403, 'Register to save products.');

        return $client;
    }
}
