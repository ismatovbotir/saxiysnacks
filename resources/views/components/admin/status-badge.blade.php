@props(['status'])

@php
    $tone = match (true) {
        in_array($status->value, ['new', 'reseller']) => ['bg-amber-50 text-amber-800 ring-amber-600/20', 'bg-amber-500'],
        in_array($status->value, ['active', 'confirmed', 'completed']) => ['bg-green-50 text-green-800 ring-green-600/20', 'bg-green-600'],
        $status->value === 'shipped' => ['bg-blue-50 text-blue-800 ring-blue-600/20', 'bg-blue-600'],
        in_array($status->value, ['blocked', 'cancelled']) => ['bg-red-50 text-red-800 ring-red-600/20', 'bg-red-600'],
        default => ['bg-black/[0.03] text-black/70 ring-black/10', 'bg-black/40'],
    };
@endphp

<span {{ $attributes->class(['admin-badge', $tone[0]]) }}><span class="size-1.5 rounded-full {{ $tone[1] }}" aria-hidden="true"></span>{{ $status->label() }}</span>
