@props(['product'])

@php($url = \App\Support\Storefront::route('product', [$product->category, $product]))

<article class="flex flex-col overflow-hidden rounded-[20px] border border-line bg-surface" data-w="{{ $product->weight_grams }}">
  <a class="relative block bg-tint px-2.5 pt-4 pb-2" href="{{ $url }}" tabindex="-1" aria-hidden="true">
    @if ($product->is_featured)
      <span class="absolute top-2.5 left-2.5 z-10 rounded-full bg-brand px-2.5 py-0.5 text-xs font-bold text-night">{{ __('site.badges.best') }}</span>
    @elseif ($product->is_new)
      <span class="absolute top-2.5 left-2.5 z-10 rounded-full bg-leaf px-2.5 py-0.5 text-xs font-bold text-white">{{ __('site.badges.new') }}</span>
    @endif
    <x-storefront.pack :product="$product" class="max-w-[120px] sm:max-w-[150px]" />
  </a>
  <div class="flex flex-1 flex-col gap-1.5 p-3 md:px-4 md:pb-4">
    <h3 class="text-sm sm:text-[17px] sm:leading-snug"><a class="hover:underline" href="{{ $url }}">{{ $product->translate('name') }}</a></h3>
    <p class="text-xs text-muted sm:text-[13px]">{{ $product->weightLabel() }} {{ __('site.g') }}@if ($product->box_quantity) · {{ $product->box_quantity }} {{ __('site.pieces') }}@endif</p>
    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-2">
      <span class="text-xs font-bold sm:text-sm">{{ __('site.priceOnReq') }}</span>
      <button class="btn btn-leaf btn-sm w-full sm:w-auto" type="button" data-add="{{ $product->slug }}">{{ __('site.add') }}</button>
    </div>
  </div>
</article>
