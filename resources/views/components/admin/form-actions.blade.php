@props(['label' => null, 'cancel' => null, 'inCard' => true])

{{-- Save bar: sticks above the mobile tab bar so the primary action is always within thumb reach. --}}
<div {{ $attributes->class([
    'sticky bottom-[calc(4.25rem+env(safe-area-inset-bottom))] z-10 flex items-center gap-2 border-black/10 bg-white/95 py-3 lg:bottom-0',
    '-mx-4 -mb-4 rounded-b-lg border-t bg-black/[0.02] px-4 sm:-mx-6 sm:-mb-6 sm:justify-end sm:px-6' => $inCard,
    'rounded-lg border px-4 shadow-sm sm:justify-end' => ! $inCard,
]) }}>
    {{ $slot }}
    @if ($cancel)
        <a href="{{ $cancel }}" class="admin-btn-outline">{{ __('Cancel') }}</a>
    @endif
    <button class="admin-btn flex-1 sm:flex-none">{{ $label ?? __('Save') }}</button>
</div>
