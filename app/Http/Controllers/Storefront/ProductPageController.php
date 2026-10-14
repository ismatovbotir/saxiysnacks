<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class ProductPageController extends Controller
{
    public function __invoke(Category $category, Product $product): View
    {
        abort_unless($category->is_active && $product->is_active, 404);

        $product->setRelation('category', $category);

        $siblings = $category->products()
            ->where('is_active', true)
            ->whereKeyNot($product->id)
            ->ordered()
            ->with('category')
            ->get();

        $related = Product::query()
            ->active()
            ->where('category_id', '!=', $category->id)
            ->where('is_featured', true)
            ->ordered()
            ->with('category')
            ->limit(4)
            ->get();

        return view('storefront.product', [
            'category' => $category,
            'product' => $product,
            'sizes' => $siblings->filter(fn (Product $sibling): bool => $sibling->translate('name') === $product->translate('name')),
            'related' => $related->isNotEmpty() ? $related : $siblings->take(4),
        ]);
    }
}
