<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()
            ->active()
            ->ordered()
            ->with(['products' => fn ($query) => $query->where('is_active', true)->ordered()])
            ->get()
            ->each(fn (Category $category) => $category->products->each->setRelation('category', $category));

        $featured = Product::query()->active()->ordered()->where('is_featured', true)->with('category')->get();

        return view('storefront.home', [
            'categories' => $categories,
            'featured' => $featured,
        ]);
    }
}
