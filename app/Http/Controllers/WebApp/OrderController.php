<?php

namespace App\Http\Controllers\WebApp;

use App\Actions\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\WebApp\PlaceOrderRequest;
use App\Http\Resources\WebAppOrderResource;
use App\Models\TelegramClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var TelegramClient|null $client */
        $client = $request->attributes->get('telegram_client');

        abort_unless($client?->isActive(), 403);

        return WebAppOrderResource::collection(
            $client->orders()->with('items')->latest('id')->limit(30)->get(),
        );
    }

    public function store(PlaceOrderRequest $request, PlaceOrder $placeOrder): JsonResponse
    {
        $order = $placeOrder->handle(
            $request->attributes->get('telegram_client'),
            $request->validated('items'),
            $request->usesSavedLocation() ? null : $request->safe()->only(['latitude', 'longitude', 'address']),
            $request->validated('comment'),
        );

        return WebAppOrderResource::make($order->load('items'))->response()->setStatusCode(201);
    }
}
