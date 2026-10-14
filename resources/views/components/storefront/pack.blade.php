@props(['product', 'eager' => false])

@php
    $label = $product->translate('name').' '.$product->weight_grams.' '.__('site.g');
    [$art, $variant] = $product->illustration ? explode(':', $product->illustration->value) : ['ball', 'plain'];
@endphp

@if ($product->image_url)
    <img src="{{ $product->image_url }}" alt="{{ $label }}" width="400" height="400" @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async"
         {{ $attributes->class('mx-auto block aspect-square w-full object-contain') }}>
@else
    <span role="img" aria-label="{{ $label }}"
          data-pack="{{ json_encode(['art' => $art, 'v' => $variant, 'band' => $product->category->color, 'w' => $product->weightLabel(), 'name' => $product->translate('name')], JSON_UNESCAPED_UNICODE) }}"
          {{ $attributes->class('mx-auto block aspect-[200/280] w-full [&>svg]:size-full') }}></span>
@endif
