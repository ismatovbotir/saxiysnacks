@php
    use App\Support\Storefront;

    $hexProducts = ($featured->count() >= 6 ? $featured : $featured->merge($categories->flatMap->products))->unique('id')->take(6)->values();
    $hexPositions = [[5, 4], [33, 0], [61, 4], [19, 29], [47, 29], [33, 56]];
    $hexTints = ['#DCE9FA', '#F7E3B5', '#F7E3B5', '#F7E3B5', '#DCE9FA', '#F6D77F'];
    $seo = [
        'title' => __('site.metaHomeTitle'),
        'description' => __('site.heroP'),
        'route' => 'home',
        'schema' => [app(\App\Support\StructuredData::class)->itemList($featured)],
    ];
@endphp

<x-storefront.layout :seo="$seo">
  <section class="relative overflow-hidden pt-7 pb-11 md:pt-14 md:pb-18">
    <svg class="pointer-events-none absolute -top-28 -right-44 size-[340px] opacity-60 md:-top-28 md:-right-36 md:size-[560px] md:opacity-95" viewBox="0 0 560 560" aria-hidden="true"><path d="M300 20C130 10 10 150 30 320c20 170 200 260 340 200-130 20-250-70-240-210C140 190 200 60 300 20Z" fill="#F59B00"/><path d="M420 200c-90-60-190-30-250 60 90-30 190-20 250-60Z" fill="#2FA354" transform="translate(70 30)"/></svg>
    <div class="relative mx-auto grid max-w-[1180px] items-center gap-8 px-4 md:grid-cols-[1.05fr_.95fr] md:px-5">
      <div>
        <h1 class="text-[32px] sm:text-5xl lg:text-[58px]">{{ __('site.heroH') }} <em class="text-leaf-ink not-italic">{{ __('site.heroEm') }}</em></h1>
        <p class="mt-5 mb-7 max-w-[46ch] text-lg text-muted">{{ __('site.heroP') }}</p>
        <div class="flex flex-wrap gap-3">
          <a class="btn btn-primary" href="{{ Storefront::route('catalog') }}">{{ __('site.seeCat') }}</a>
          <a class="btn btn-outline" href="{{ Storefront::route('contact') }}">{{ __('site.getPrice') }}</a>
        </div>
        <ul class="mt-7 flex flex-wrap gap-x-5 gap-y-2 text-sm font-semibold">
          @foreach (['f1', 'f2', 'f3'] as $fact)
            <li class="flex items-center gap-2"><span class="size-2.5 rounded-full bg-leaf"></span>{{ __('site.'.$fact) }}</li>
          @endforeach
        </ul>
      </div>
      <div class="relative mx-auto aspect-square w-full max-w-[400px] md:mr-0 md:ml-auto md:max-w-[520px]">
        @foreach ($hexProducts as $hexProduct)
          <a class="absolute block aspect-[1/1.1547] w-[38%] drop-shadow-[0_10px_14px_rgba(27,38,22,.22)] transition-transform [clip-path:polygon(25%_0,75%_0,100%_50%,75%_100%,25%_100%,0_50%)] hover:-translate-y-1.5"
             style="left:{{ $hexPositions[$loop->index][0] }}%;top:{{ $hexPositions[$loop->index][1] }}%;background:{{ $hexTints[$loop->index] }}"
             href="{{ Storefront::route('product', [$hexProduct->category, $hexProduct]) }}" aria-label="{{ $hexProduct->translate('name') }} {{ $hexProduct->weight_grams }} {{ __('site.g') }}">
            <x-storefront.pack :product="$hexProduct" :eager="$loop->first" class="absolute inset-[6%] size-[88%] max-w-none" />
          </a>
        @endforeach
      </div>
    </div>
  </section>

  <div class="bg-night py-5 text-[#F1F6EA]">
    <div class="mx-auto grid max-w-[1180px] grid-cols-2 gap-4 px-4 md:grid-cols-4 md:px-5">
      @foreach ([1, 2, 3, 4] as $i)
        <div><b class="block font-display text-xl text-brand md:text-[22px]">{{ __("site.t{$i}") }}</b><span class="text-sm opacity-85">{{ __("site.t{$i}s") }}</span></div>
      @endforeach
    </div>
  </div>

  <section class="mx-auto max-w-[1180px] px-4 py-10 md:px-5 md:py-16">
    <div class="mb-6 md:mb-7">
      <h2 class="text-2xl md:text-4xl">{{ __('site.catsH') }}</h2>
      <p class="mt-2.5 max-w-[52ch] text-muted">{{ __('site.catsP') }}</p>
    </div>
    <div class="grid grid-cols-2 gap-3 md:gap-4 lg:grid-cols-4">
      @foreach ($categories as $category)
        <a href="{{ Storefront::route('category', $category) }}"
           class="{{ ['bg-tint', 'bg-sun', 'bg-sky', 'bg-peach'][$loop->index % 4] }} flex min-h-56 flex-col overflow-hidden rounded-[26px_26px_26px_6px] p-4 transition-transform hover:-translate-y-1 md:min-h-72 md:p-5">
          <div class="grid flex-1 place-items-center py-2">
            @if ($category->products->first())<x-storefront.pack :product="$category->products->first()" class="max-w-[100px] sm:max-w-[150px]" />@endif
          </div>
          <h3 class="text-base md:text-[17px]">{{ $category->translate('name') }}</h3>
          <small class="font-medium text-muted">{{ $category->products->count() }} {{ __('site.products') }} · {{ $category->products->pluck('weight_grams')->unique()->sort()->implode(', ') }} {{ __('site.g') }}</small>
        </a>
      @endforeach
    </div>
  </section>

  @if ($featured->isNotEmpty())
    <section class="mx-auto max-w-[1180px] px-4 pb-10 md:px-5 md:pb-16">
      <div class="mb-6 flex flex-wrap items-end justify-between gap-4 md:mb-7">
        <div><h2 class="text-2xl md:text-4xl">{{ __('site.bestH') }}</h2><p class="mt-2.5 max-w-[52ch] text-muted">{{ __('site.bestP') }}</p></div>
        <a class="btn btn-outline btn-sm" href="{{ Storefront::route('catalog') }}">{{ __('site.seeCat') }}</a>
      </div>
      <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4">
        @foreach ($featured->take(8) as $product)<x-storefront.product-card :product="$product" />@endforeach
      </div>
    </section>
  @endif

  <section class="mx-auto max-w-[1180px] px-4 pb-10 md:px-5 md:pb-16">
    <h2 class="mb-6 text-2xl md:mb-7 md:text-4xl">{{ __('site.whyH') }}</h2>
    <div class="grid border-t-2 border-ink md:grid-cols-3">
      @foreach ([1, 2, 3] as $i)
        <div class="border-b border-line py-6 md:pr-6 md:[&+&]:border-l md:[&+&]:pl-6">
          <h3 class="mb-2 text-[17px]">{{ __("site.w{$i}") }}</h3>
          <p class="text-muted">{{ __("site.w{$i}p") }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <section class="mx-auto grid max-w-[1180px] items-center gap-8 px-4 pb-10 md:grid-cols-2 md:gap-9 md:px-5 md:pb-16">
    <div>
      <h2 class="text-2xl md:text-4xl">{{ __('site.storyH') }}</h2>
      <p class="mt-3.5 max-w-[52ch] text-muted">{{ __('site.storyP') }}</p>
    </div>
    <blockquote class="rounded-[26px] bg-sun p-6 md:p-7"><p class="text-lg leading-snug font-semibold md:text-[19px]">{{ __('site.quote') }}</p></blockquote>
  </section>

  @if ($telegramBotUrl)
    <section class="mx-auto max-w-[1180px] px-4 pb-10 md:px-5 md:pb-16"><x-storefront.telegram-cta :url="$telegramBotUrl" /></section>
  @endif

  @if ($featured->isNotEmpty())
    <section class="mx-auto max-w-[1180px] px-4 pb-10 md:px-5 md:pb-16">
      <div class="grid items-center gap-6 rounded-[34px_34px_34px_8px] bg-leaf p-6 text-white md:grid-cols-[1.2fr_.8fr] md:p-10">
        <div>
          <h2 class="text-2xl md:text-4xl">{{ __('site.bundleH') }}</h2>
          <p class="mt-3 mb-5 max-w-[48ch] opacity-90">{{ __('site.bundleP') }}</p>
          <button class="btn btn-primary" type="button" data-open="1">{{ __('site.bundleBtn') }}</button>
        </div>
        <ul class="grid gap-2.5">
          @foreach ($featured->unique('category_id')->take(3) as $product)
            <li class="flex items-center justify-between gap-3 rounded-2xl bg-white/15 px-4 py-3 font-semibold">
              <span>{{ $product->translate('name') }} {{ $product->weightLabel() }} {{ __('site.g') }}</span>
              <button class="btn btn-primary btn-sm" type="button" data-add="{{ $product->slug }}" aria-label="{{ __('site.add') }}">+</button>
            </li>
          @endforeach
        </ul>
      </div>
    </section>
  @endif
</x-storefront.layout>
