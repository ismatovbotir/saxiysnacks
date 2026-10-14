@php
    use App\Support\Storefront;

    $structuredData = app(\App\Support\StructuredData::class);
    $title = $category?->translate('name') ?? __('site.catalog');
    $intro = $category?->translate('description') ?: __('site.catsP');
    $weights = $products->pluck('weight_grams')->unique()->sort()->values();
    $crumbs = [[__('site.home'), Storefront::route('home')], [__('site.catalog'), Storefront::route('catalog')]];

    if ($category) {
        $crumbs[] = [$title, Storefront::route('category', $category)];
    }

    $seo = [
        'title' => $category ? __('site.metaCategoryTitle', ['category' => $title]) : __('site.metaCatalogTitle'),
        'description' => $category ? $intro.' '.$products->map(fn ($product) => $product->translate('name').' '.$product->weight_grams.' '.__('site.g'))->unique()->implode(', ') : __('site.heroP'),
        'route' => $category ? 'category' : 'catalog',
        'params' => $category ? [$category] : [],
        'image' => $products->firstWhere('image_url')?->image_url,
        'breadcrumbs' => $crumbs,
        'schema' => [array_merge(['@type' => 'CollectionPage', 'name' => $title, 'description' => $intro, 'url' => Storefront::route($category ? 'category' : 'catalog', $category ? [$category] : [])], ['mainEntity' => $structuredData->itemList($products)])],
    ];
@endphp

<x-storefront.layout :seo="$seo">
  <div class="mx-auto max-w-[1180px] px-4 pb-14 md:px-5">
    <div class="grid gap-4 pt-2 pb-6 md:grid-cols-[1fr_auto] md:items-end">
      <div>
        <h1 class="text-[28px] md:text-[44px]">{{ $title }}</h1>
        <p class="mt-2.5 max-w-[56ch] text-muted">{{ $intro }}</p>
      </div>
      <label class="grid gap-1.5 text-sm font-semibold">{{ __('site.sort') }}
        <select id="sortSel" class="rounded-xl border-[1.5px] border-line bg-surface px-3 py-2.5 text-ink">
          <option value="def">{{ __('site.sDef') }}</option><option value="w">{{ __('site.sW') }}</option><option value="wd">{{ __('site.sWd') }}</option>
        </select>
      </label>
    </div>

    <nav class="-mx-4 mb-2.5 flex gap-2 overflow-x-auto px-4 pb-2.5 [scrollbar-width:none] md:mx-0 md:px-0" aria-label="{{ __('site.catalog') }}">
      <a class="chip" href="{{ Storefront::route('catalog') }}" @if (! $category) aria-current="page" @endif>{{ __('site.all') }}</a>
      @foreach ($navCategories as $navCategory)
        <a class="chip" href="{{ Storefront::route('category', $navCategory) }}" @if ($category?->is($navCategory)) aria-current="page" @endif>{{ $navCategory->translate('name') }}</a>
      @endforeach
    </nav>

    @if ($weights->count() > 1)
      <div class="mb-6 flex flex-wrap gap-2" role="group" aria-label="{{ __('site.weight') }}">
        <button class="chip" type="button" aria-pressed="true" data-w="all">{{ __('site.all') }}</button>
        @foreach ($weights as $weight)
          <button class="chip" type="button" aria-pressed="false" data-w="{{ $weight }}">{{ $weight }} {{ __('site.g') }}</button>
        @endforeach
      </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4" id="grid">
      @foreach ($products as $product)<x-storefront.product-card :product="$product" />@endforeach
    </div>

    @if ($telegramBotUrl)<x-storefront.telegram-cta :url="$telegramBotUrl" class="mt-10" />@endif
  </div>
</x-storefront.layout>
