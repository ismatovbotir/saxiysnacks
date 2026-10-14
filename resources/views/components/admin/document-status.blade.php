@props(['status'])

@php
    $tone = match ($status) {
        \App\Enums\StockDocumentStatus::Posted => ['bg-green-50 text-green-800 ring-green-600/20', 'bg-green-600'],
        \App\Enums\StockDocumentStatus::Cancelled => ['bg-red-50 text-red-800 ring-red-600/20', 'bg-red-600'],
        default => ['bg-amber-50 text-amber-800 ring-amber-600/20', 'bg-amber-500'],
    };
@endphp

<span {{ $attributes->class(['admin-badge', $tone[0]]) }}><span class="size-1.5 rounded-full {{ $tone[1] }}" aria-hidden="true"></span>{{ $status->label() }}</span>
