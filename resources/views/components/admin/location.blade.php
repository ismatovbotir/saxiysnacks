@props(['latitude', 'longitude', 'address' => null])

@if ($latitude !== null && $longitude !== null)
    <div {{ $attributes->class('text-sm') }}>
        @if ($address)<p class="font-medium">{{ $address }}</p>@endif
        <p class="font-mono text-xs text-black/50">{{ number_format($latitude, 5) }}, {{ number_format($longitude, 5) }}</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <a href="{{ \App\Support\Maps::google($latitude, $longitude) }}" target="_blank" rel="noopener" class="admin-btn-outline px-3 py-1.5">Google Maps ↗</a>
            <a href="{{ \App\Support\Maps::yandex($latitude, $longitude) }}" target="_blank" rel="noopener" class="admin-btn-outline px-3 py-1.5">Yandex Maps ↗</a>
        </div>
    </div>
@else
    <p {{ $attributes->class('text-sm text-amber-700') }}>No location yet.</p>
@endif
