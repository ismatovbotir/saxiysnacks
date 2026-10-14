<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        return view('storefront.catalog', [
            'category' => null,
            'products' => Product::query()->active()->ordered()->with('category')->get(),
        ]);
    }

    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        return view('storefront.catalog', [
            'category' => $category,
            'products' => $category->products()->where('is_active', true)->ordered()->with('category')->get(),
        ]);
    }
}
