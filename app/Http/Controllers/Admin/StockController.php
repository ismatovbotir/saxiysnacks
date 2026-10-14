<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Single warehouse, counted in pieces: balances per product and the movement journal. Stock changes only through documents.
 */
class StockController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->withStockBalance()
            ->when($request->integer('category'), fn ($query, int $category) => $query->where('category_id', $category))
            ->when($request->string('search')->trim()->value(), fn ($query, string $search) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"),
            ))
            ->ordered()
            ->get();

        return view('admin.stock.index', [
            'products' => $products,
            'categories' => Category::query()->ordered()->get(),
            'totalPieces' => (int) $products->sum('stock_balance'),
            'outOfStock' => $products->filter(fn (Product $product): bool => (int) $product->stock_balance <= 0)->count(),
        ]);
    }

    public function movements(Request $request): View
    {
        $type = StockMovementType::tryFrom($request->string('type')->value());

        $movements = StockMovement::query()
            ->with(['product', 'order', 'document', 'user'])
            ->when($request->integer('product'), fn ($query, int $product) => $query->where('product_id', $product))
            ->when($type, fn ($query, StockMovementType $type) => $query->where('type', $type))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.stock.movements', [
            'movements' => $movements,
            'type' => $type,
            'product' => $request->integer('product') ? Product::find($request->integer('product')) : null,
        ]);
    }
}
