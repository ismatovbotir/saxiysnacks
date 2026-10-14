<x-admin.layout :title="__('Stock movements')">
    <x-admin.page-header :title="__('Stock movements')" :subtitle="$product ? $product->translate('name', app()->getLocale()) : __('Every change to the warehouse, newest first.')">
        <a href="{{ route('admin.stock.index') }}" class="admin-btn-outline">← {{ __('Stock') }}</a>
    </x-admin.page-header>

    <div class="admin-filterbar">
        <nav class="admin-chips" aria-label="{{ __('Filter by type') }}">
            <a href="{{ route('admin.stock.movements', request()->only('product')) }}" class="admin-chip" @if ($type === null) aria-current="page" @endif>{{ __('All') }}</a>
            @foreach (\App\Enums\StockMovementType::cases() as $case)
                <a href="{{ route('admin.stock.movements', [...request()->only('product'), 'type' => $case->value]) }}" class="admin-chip" @if ($type === $case) aria-current="page" @endif>{{ $case->label() }}</a>
            @endforeach
        </nav>
        @if ($product)
            <a href="{{ route('admin.stock.movements', request()->only('type')) }}" class="admin-btn-outline h-8 px-2.5 sm:ml-auto">{{ __('All products') }} ×</a>
        @endif
    </div>

    <div class="admin-card overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr><th class="admin-th">{{ __('Date') }}</th><th class="admin-th">{{ __('Product') }}</th><th class="admin-th">{{ __('Type') }}</th><th class="admin-th text-right">{{ __('Pieces') }}</th><th class="admin-th">{{ __('Document') }}</th><th class="admin-th">{{ __('Note') }}</th><th class="admin-th">{{ __('By') }}</th></tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td class="admin-td whitespace-nowrap text-black/60">{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                        <td class="admin-td"><a href="{{ route('admin.stock.movements', ['product' => $movement->product_id]) }}" class="font-medium hover:underline">{{ $movement->product->translate('name', app()->getLocale()) }}</a> <span class="text-xs text-black/50">{{ $movement->product->weight_grams }} {{ __('g') }}</span></td>
                        <td class="admin-td"><span class="admin-badge bg-black/[0.03] text-black/70 ring-black/10">{{ $movement->type->label() }}</span></td>
                        <td @class(['admin-td text-right admin-kbd-num font-semibold', 'text-leaf-dark' => $movement->quantity > 0, 'text-red-700' => $movement->quantity < 0])>{{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 0, '.', ' ') }}</td>
                        <td class="admin-td whitespace-nowrap">
                            @if ($movement->document)<a href="{{ route('admin.stock.documents.show', $movement->document) }}" class="font-mono font-medium hover:underline">{{ $movement->document->number }}</a>@else<span class="text-black/30">—</span>@endif
                            @if ($movement->order)<a href="{{ route('admin.orders.show', $movement->order) }}" class="block text-xs text-black/50 hover:underline">{{ $movement->order->number() }}</a>@endif
                        </td>
                        <td class="admin-td text-black/70">{{ $movement->document?->counterparty ?? $movement->note }}</td>
                        <td class="admin-td whitespace-nowrap text-black/60">{{ $movement->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="admin-td py-10 text-center text-black/50">{{ __('No stock movements yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $movements->links() }}</div>
</x-admin.layout>
