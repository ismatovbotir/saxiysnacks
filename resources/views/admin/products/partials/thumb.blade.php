@if ($product->image_url)
    <img src="{{ $product->image_url }}" alt="" class="size-12 shrink-0 rounded-lg object-cover">
@else
    <span class="grid size-12 shrink-0 place-items-center rounded-lg text-xs font-bold text-white" style="background: {{ $product->category->color }}">{{ $product->weight_grams }}{{ __('g') }}</span>
@endif
