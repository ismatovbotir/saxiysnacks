<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->when($request->integer('category'), fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->string('search')->trim()->value(), fn ($query, string $search) => $query->where(
                fn ($query) => $query->where('name->ru', 'like', "%{$search}%")
                    ->orWhere('name->uz', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%"),
            ))
            ->ordered()
            ->paginate(25)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true]),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($this->attributes($request));

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($this->attributes($request, $product));

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(ProductRequest $request, ?Product $product = null): array
    {
        $attributes = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($product?->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $attributes['image_path'] = $request->file('image')?->store('products', 'public');
        }

        return $attributes;
    }
}
