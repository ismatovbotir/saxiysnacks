@php
    use App\Support\Storefront;

    $name = $product->translate('name');
    $description = $product->translate('description');
    $boxText = $product->box_quantity ? __('site.metaBox', ['count' => $product->box_quantity]) : '';
    $seo = [
        'title' => __('site.metaProductTitle', ['name' => $name, 'weight' => $product->weight_grams]),
        'description' => __('site.metaProductDescription', ['name' => $name, 'weight' => $product->weight_grams, 'box' => $boxText]).($description ? ' '.$description : ''),
        'route' => 'product',
        'params' => [$category, $product],
        'image' => $product->image_url,
        'type' => 'product',
        'breadcrumbs' => [
            [__('site.home'), Storefront::route('home')],
            [__('site.catalog'), Storefront::route('catalog')],
            [$category->translate('name'), Storefront::route('category', $category)],
            [$name.' '.$product->weight_grams.' '.__('site.g'), Storefront::route('product', [$category, $product])],
        ],
        'schema' => [app(\App\Support\StructuredData::class)->product($product, $category)],
    ];
@endphp

<x-storefront.layout :seo="$seo">
  <div class="mx-auto max-w-[1180px] px-4 pb-14 md:px-5">
    <article class="grid gap-5 pt-3 pb-10 md:grid-cols-2 md:gap-10">
      <div class="grid place-items-center self-start rounded-[34px_34px_34px_8px] bg-tint p-6 md:sticky md:top-20 md:p-9">
        <x-storefront.pack :product="$product" eager class="max-w-[200px] md:max-w-[260px]" />
      </div>
      <div>
        <h1 class="text-[26px] md:text-[40px]">{{ $name }} {{ $product->weight_grams }} {{ __('site.g') }}</h1>
        <p class="mt-2 text-muted">{{ $category->translate('name') }} · {{ $product->weightLabel() }} {{ __('site.g') }}@if ($product->box_quantity) · {{ $product->box_quantity }} {{ __('site.pieces') }}@endif</p>

        <table class="my-4 w-full border-collapse text-[15px]"><tbody>
          @foreach (array_filter([
              __('site.brand') => $site->get('brand_name'),
              __('site.weight') => $product->weightLabel().' '.__('site.g'),
              __('site.box') => $product->box_quantity ? $product->box_quantity.' '.__('site.pieces') : null,
              __('site.sku') => $product->sku,
          ]) as $label => $value)
            <tr class="border-b border-line"><th scope="row" class="w-[42%] py-2.5 text-left align-top font-medium text-muted">{{ $label }}</th><td class="py-2.5">{{ $value }}</td></tr>
          @endforeach
        </tbody></table>

        @if ($description)
          <h2 class="mb-2 text-lg">{{ __('site.description') }}</h2>
          <p class="mb-5 max-w-[60ch] whitespace-pre-line text-muted">{{ $description }}</p>
        @endif

        @if ($sizes->isNotEmpty())
          <b>{{ __('site.sizes') }}</b>
          <div class="mt-2.5 mb-5 flex flex-wrap gap-2">
            @foreach ($sizes as $size)
              <a class="chip" href="{{ Storefront::route('product', [$category, $size]) }}">{{ $size->weightLabel() }} {{ __('site.g') }}@if ($size->box_quantity) · {{ $size->box_quantity }} {{ __('site.pcs') }}@endif</a>
            @endforeach
          </div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
          <div class="inline-flex items-center rounded-full border-[1.5px] border-line bg-surface">
            <button type="button" aria-label="−" data-q="-1" class="h-11 w-11 text-xl font-bold">−</button>
            <span id="iqv" class="min-w-8 text-center font-bold">{{ $product->minQuantityFor(\App\Enums\ClientType::Consumer) }}</span><span class="text-sm text-muted">{{ __('site.pcs') }}</span>
            <button type="button" aria-label="+" data-q="1" class="h-11 w-11 text-xl font-bold">+</button>
          </div>
          <button class="btn btn-leaf grow sm:grow-0" type="button" id="iadd" data-id="{{ $product->slug }}">{{ __('site.add') }}</button>
          <a class="btn btn-outline w-full sm:w-auto" href="{{ Storefront::route('contact') }}">{{ __('site.askPrice') }}</a>
        </div>
        <p class="mt-5 rounded-2xl bg-sun px-4 py-3 text-sm">{{ __('site.noteItem') }}</p>
      </div>
    </article>

    @if ($telegramBotUrl)<x-storefront.telegram-cta :url="$telegramBotUrl" />@endif

    @if ($related->isNotEmpty())
      <section class="pt-10">
        <h2 class="mb-5 text-2xl">{{ __('site.related') }}</h2>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4">
          @foreach ($related as $relatedProduct)<x-storefront.product-card :product="$relatedProduct" />@endforeach
        </div>
      </section>
    @endif
  </div>
</x-storefront.layout>
