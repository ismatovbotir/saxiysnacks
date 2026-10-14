<x-admin.layout :title="__('Stock')">
    <x-admin.page-header :title="__('Stock')" :subtitle="__('One warehouse, counted in pieces. Stock changes through posted documents; shipped orders post their sales invoice automatically.')">
        @can('access-stock_movements')
            <a href="{{ route('admin.stock.movements') }}" class="admin-btn-outline"><x-admin.icon name="list" class="size-4" /> {{ __('Stock movements') }}</a>
        @endcan
    </x-admin.page-header>

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-6">
        <div class="min-w-0 space-y-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ([
                    [__('Products'), $products->count(), null],
                    [__('Pieces in stock'), number_format($totalPieces, 0, '.', ' '), null],
                    [__('Out of stock'), $outOfStock, $outOfStock > 0 ? 'warning' : null],
                ] as [$label, $value, $tone])
                    <div @class(['admin-card p-4', 'border-amber-300 bg-amber-50/60' => $tone === 'warning'])>
                        <span class="block text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ $label }}</span>
                        <span class="mt-1 block text-2xl font-semibold tabular-nums">{{ $value }}</span>
                    </div>
                @endforeach
            </div>

            <form method="GET" class="flex flex-wrap gap-2">
                <select name="category" class="admin-input w-auto" onchange="this.form.submit()" aria-label="{{ __('Category') }}">
                    <option value="">{{ __('All categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request()->integer('category') === $category->id)>{{ $category->translate('name', app()->getLocale()) }}</option>
                    @endforeach
                </select>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name or SKU') }}" class="admin-input min-w-0 flex-1 sm:max-w-64">
                <button class="admin-btn-outline">{{ __('Search') }}</button>
            </form>

            <div class="admin-card overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr><th class="admin-th">{{ __('Product') }}</th><th class="admin-th">{{ __('Category') }}</th><th class="admin-th text-right">{{ __('Pieces') }}</th><th class="admin-th text-right">{{ __('Packages') }}</th><th class="admin-th"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php($balance = (int) $product->stock_balance)
                            <tr>
                                <td class="admin-td">
                                    <span class="font-medium">{{ $product->translate('name', app()->getLocale()) }}</span>
                                    <span class="block text-xs text-black/50">{{ $product->weight_grams }} {{ __('g') }}{{ $product->sku ? ' · '.$product->sku : '' }}</span>
                                </td>
                                <td class="admin-td text-black/70">{{ $product->category->translate('name', app()->getLocale()) }}</td>
                                <td @class(['admin-td text-right admin-kbd-num', 'font-semibold text-red-700' => $balance < 0, 'text-amber-700' => $balance === 0])>{{ number_format($balance, 0, '.', ' ') }}</td>
                                <td class="admin-td text-right admin-kbd-num text-black/60">
                                    @if ($product->box_quantity && $balance > 0)
                                        {{ intdiv($balance, $product->box_quantity) }}@if ($balance % $product->box_quantity) <span class="text-xs">+{{ $balance % $product->box_quantity }} {{ __('pcs') }}</span>@endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="admin-td text-right">@can('access-stock_movements')<a href="{{ route('admin.stock.movements', ['product' => $product->id]) }}" class="admin-icon-btn" title="{{ __('Stock movements') }}" aria-label="{{ __('Stock movements') }}"><x-admin.icon name="list" class="size-4" /></a>@endcan</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="admin-td py-10 text-center text-black/50">{{ __('No products found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="space-y-3">
            @if (collect(\App\Enums\StockDocumentType::cases())->contains(fn ($case) => auth()->user()->canAccess($case->permission())))
                <div class="admin-card overflow-hidden lg:sticky lg:top-20">
                    <div class="admin-card-header"><h2 class="admin-card-title">{{ __('New document') }}</h2></div>
                    <ul class="divide-y divide-black/[0.06]">
                        @foreach (\App\Enums\StockDocumentType::cases() as $case)
                            @cannot('access-'.$case->permission()->value) @continue @endcannot
                            <li>
                                <a href="{{ route('admin.stock.'.$case->slug().'.create') }}" class="flex items-start gap-3 px-4 py-3 hover:bg-brand/[0.04] sm:px-5">
                                    <span class="grid size-8 shrink-0 place-items-center rounded-md bg-black/[0.04]"><x-admin.icon :name="$case->icon()" class="size-4" /></span>
                                    <span class="text-sm"><span class="block font-semibold">{{ $case->label() }}</span><span class="block text-xs text-black/55">{{ $case->hint() }}</span></span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>
</x-admin.layout>
