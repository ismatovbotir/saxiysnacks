<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatalogProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $client = $request->attributes->get('telegram_client');

        $categories = Category::query()->active()->ordered()->get(['id', 'slug', 'name', 'color']);
        $products = Product::query()->active()->ordered()->with('category:id,color')->get();

        return response()->json([
            'categories' => $categories,
            'products' => CatalogProductResource::collection($products)->resolve($request),
            'saved' => $client?->isActive() ? (object) $client->savedQuantities() : (object) [],
        ]);
    }
}
