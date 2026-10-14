{{-- Minimum order and step per customer type, shown only when they differ from "any number of boxes". --}}
@foreach ([\App\Enums\ClientType::Consumer, \App\Enums\ClientType::Reseller] as $type)
    @if ($product->minQuantityFor($type) > 1 || $product->quantityStepFor($type) > 1)
        <span class="block text-xs text-black/50">{{ __('min :min · step :step', ['min' => $product->minQuantityFor($type), 'step' => $product->quantityStepFor($type)]) }}</span>
    @endif
@endforeach
