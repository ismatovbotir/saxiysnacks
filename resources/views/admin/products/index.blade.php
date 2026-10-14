<x-admin.layout :title="__('Products')">
    <x-admin.page-header :title="__('Products')" :subtitle="trans_choice(':count product|:count products', $products->total())">
        <a href="{{ route('admin.products.create') }}" class="admin-btn"><x-admin.icon name="plus" class="size-4" /> {{ __('New product') }}</a>
    </x-admin.page-header>

    <div class="admin-filterbar">
        <nav class="admin-chips" aria-label="{{ __('Filter by category') }}">
            <a href="{{ route('admin.products.index', request()->only('search')) }}" class="admin-chip" @if (! request()->integer('category')) aria-current="page" @endif>{{ __('All') }}</a>
            @foreach ($categories as $category)
                <a href="{{ route('admin.products.index', [...request()->only('search'), 'category' => $category->id]) }}" class="admin-chip" @if (request()->integer('category') === $category->id) aria-current="page" @endif>
                    <span class="size-2 rounded-full" style="background: {{ $category->color }}"></span>{{ $category->translate('name', app()->getLocale()) }}
                </a>
            @endforeach
        </nav>
        <form method="GET" class="flex w-full gap-2 sm:ml-auto sm:w-auto">
            @if (request()->integer('category'))<input type="hidden" name="category" value="{{ request()->integer('category') }}">@endif
            <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or SKU') }}" class="admin-input sm:w-56">
            <button class="admin-btn-outline">{{ __('Search') }}</button>
        </form>
    </div>

    <div class="space-y-2 md:hidden">
        @forelse ($products as $product)
            <a href="{{ route('admin.products.edit', $product) }}" class="admin-card flex items-center gap-3 p-3 active:bg-black/2">
                @include('admin.products.partials.thumb')
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-semibold">{{ $product->translate('name', app()->getLocale()) }}</span>
                    <span class="block truncate text-xs text-black/50">{{ $product->category->translate('name', app()->getLocale()) }} · {{ $product->weight_grams }} {{ __('g') }}{{ $product->box_quantity ? ' · '.__(':count pcs', ['count' => $product->box_quantity]) : '' }}</span>
                    @if ($product->gtin || $product->class_code)<span class="block truncate font-mono text-[11px] text-black/50">{{ collect([$product->gtin ? 'GTIN '.$product->gtin : null, $product->class_code ? 'MXIK '.$product->class_code : null])->filter()->implode(' · ') }}</span>@endif
                    @if ($product->consumer_price)
                        <span class="block text-sm font-semibold">{{ \App\Support\Money::format($product->consumer_price) }} <span class="font-normal text-black/50">· ⭐ {{ $product->reseller_price ? \App\Support\Money::format($product->reseller_price) : __('same') }}</span></span>
                    @else
                        <span class="block text-sm text-amber-700">{{ __('No price — not orderable') }}</span>
                    @endif
                </span>
                <span class="flex shrink-0 flex-col items-end gap-1 text-xs">
                    @unless ($product->is_active)<span class="rounded-full bg-black/5 px-2 py-0.5">{{ __('Hidden') }}</span>@endunless
                    @if ($product->is_featured)<span class="rounded-full bg-brand/15 px-2 py-0.5">{{ __('Hit') }}</span>@endif
                    @if ($product->is_new)<span class="rounded-full bg-leaf/15 px-2 py-0.5">{{ __('New item') }}</span>@endif
                    <x-admin.icon name="chevron" class="size-4 text-black/30" />
                </span>
            </a>
        @empty
            <p class="admin-card px-4 py-10 text-center text-sm text-black/50">{{ __('No products found.') }}</p>
        @endforelse
    </div>

    <div class="admin-card hidden overflow-x-auto md:block">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-black/10">
                <tr><th class="admin-th">{{ __('Product') }}</th><th class="admin-th">{{ __('Category') }}</th><th class="admin-th">{{ __('Weight / box') }}</th><th class="admin-th">{{ __('Codes') }}</th><th class="admin-th">{{ __('Consumer / reseller price') }}</th><th class="admin-th">{{ __('Status') }}</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @forelse ($products as $product)
                    <tr class="hover:bg-black/2">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @include('admin.products.partials.thumb')
                                <span>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold hover:underline">{{ $product->translate('name', app()->getLocale()) }}</a>
                                    <span class="block font-mono text-xs text-black/50">{{ $product->slug }}{{ $product->sku ? ' · '.$product->sku : '' }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $product->category->translate('name', app()->getLocale()) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $product->weight_grams }} {{ __('g') }}{{ $product->box_quantity ? ' · '.__(':count pcs', ['count' => $product->box_quantity]) : '' }}</td>
                        <td class="px-4 py-3 font-mono text-xs whitespace-nowrap text-black/60">
                            @if ($product->gtin)<span class="block">GTIN {{ $product->gtin }}</span>@endif
                            @if ($product->class_code)<span class="block">MXIK {{ $product->class_code }}</span>@endif
                            @if (! $product->gtin && ! $product->class_code)—@endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if ($product->consumer_price)
                                {{ \App\Support\Money::format($product->consumer_price) }}
                                <span class="block text-xs text-black/50">⭐ {{ $product->reseller_price ? \App\Support\Money::format($product->reseller_price) : __('same as consumer') }}</span>
                            @else
                                <span class="text-amber-700">{{ __('No price — not orderable') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ $product->is_active ? __('Visible') : __('Hidden') }}
                            @if ($product->is_featured) <span class="ml-1 rounded bg-brand/15 px-1.5 text-xs">{{ __('Hit') }}</span> @endif
                            @if ($product->is_new) <span class="ml-1 rounded bg-leaf/15 px-1.5 text-xs">{{ __('New item') }}</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn-outline px-3 py-1.5">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm(@js(__('Delete this product?')))">
                                @csrf @method('DELETE')
                                <button class="admin-btn-outline px-3 py-1.5 text-red-700">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-black/50">{{ __('No products found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</x-admin.layout>
