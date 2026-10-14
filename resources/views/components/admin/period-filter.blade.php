@props(['period'])

{{-- From–to date filter for finance pages; keeps other query filters. --}}
<form method="GET" {{ $attributes->class('flex flex-wrap items-end gap-2') }}>
    @foreach (request()->except(['from', 'to', 'page']) as $key => $value)
        @if (is_string($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
    @endforeach
    <label class="text-xs font-medium text-black/60">{{ __('From') }}
        <input type="date" name="from" value="{{ $period->from->toDateString() }}" class="admin-input mt-0.5 w-auto">
    </label>
    <label class="text-xs font-medium text-black/60">{{ __('To') }}
        <input type="date" name="to" value="{{ $period->to->toDateString() }}" class="admin-input mt-0.5 w-auto">
    </label>
    <button class="admin-btn-outline">{{ __('Apply') }}</button>
    <span class="flex gap-1">
        @foreach ([__('This month') => [today()->startOfMonth(), today()], __('Last month') => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()], __('This year') => [today()->startOfYear(), today()]] as $label => [$from, $to])
            <a href="{{ request()->fullUrlWithQuery(['from' => $from->toDateString(), 'to' => $to->toDateString(), 'page' => null]) }}" class="admin-btn-outline h-9 px-2.5 text-xs">{{ $label }}</a>
        @endforeach
    </span>
</form>
