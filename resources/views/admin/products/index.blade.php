<x-admin.layout title="Products">
    <x-admin.page-header title="Products">
        <a href="{{ route('admin.products.create') }}" class="admin-btn">+ New product</a>
    </x-admin.page-header>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or SKU" class="admin-input max-w-xs">
        <select name="category" class="admin-input max-w-xs" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request()->integer('category') === $category->id)>{{ $category->translate('name', 'ru') }}</option>
            @endforeach
        </select>
        <button class="admin-btn-outline">Filter</button>
    </form>

    <div class="admin-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-black/10 text-black/60">
                <tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Weight / box</th><th class="px-4 py-3">Consumer / reseller price</th><th class="px-4 py-3">Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="" class="size-10 rounded object-cover">
                                @else
                                    <span class="grid size-10 place-items-center rounded text-xs font-bold text-white" style="background: {{ $product->category->color }}">{{ $product->weight_grams }}g</span>
                                @endif
                                <span>
                                    <span class="font-semibold">{{ $product->translate('name', 'ru') }}</span>
                                    <span class="block font-mono text-xs text-black/50">{{ $product->slug }}{{ $product->sku ? ' · '.$product->sku : '' }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $product->category->translate('name', 'ru') }}</td>
                        <td class="px-4 py-3">{{ $product->weight_grams }} g{{ $product->box_quantity ? ' · '.$product->box_quantity.' pcs' : '' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if ($product->consumer_price)
                                {{ \App\Support\Money::format($product->consumer_price) }}
                                <span class="block text-xs text-black/50">⭐ {{ $product->reseller_price ? \App\Support\Money::format($product->reseller_price) : 'same as consumer' }}</span>
                            @else
                                <span class="text-amber-700">Not set — not orderable</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ $product->is_active ? 'Visible' : 'Hidden' }}
                            @if ($product->is_featured) <span class="ml-1 rounded bg-brand/15 px-1.5 text-xs">Hit</span> @endif
                            @if ($product->is_new) <span class="ml-1 rounded bg-leaf/15 px-1.5 text-xs">New</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn-outline px-3 py-1.5">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="admin-btn-outline px-3 py-1.5 text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-black/50">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</x-admin.layout>
