<x-admin.layout :title="__('Orders')">
    <x-admin.page-header :title="__('Orders')" :subtitle="trans_choice(':count order|:count orders', $orders->total()).($status ? ' · '.$status->label() : '')" />

    <div class="admin-filterbar">
        <nav class="admin-chips" aria-label="{{ __('Filter by status') }}">
            <a href="{{ route('admin.orders.index') }}" class="admin-chip" @if ($status === null) aria-current="page" @endif>{{ __('All') }}</a>
            @foreach (\App\Enums\OrderStatus::cases() as $case)
                <a href="{{ route('admin.orders.index', ['status' => $case->value]) }}" class="admin-chip" @if ($status === $case) aria-current="page" @endif>{{ $case->emoji() }} {{ $case->label() }}</a>
            @endforeach
        </nav>
    </div>

    {{-- Phones: one card per order --}}
    <div class="space-y-2 md:hidden">
        @forelse ($orders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="admin-card block p-4 active:bg-black/2">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $order->number() }} <span class="font-normal text-black/50">· {{ $order->created_at->format('d.m H:i') }}</span></p>
                        <p class="truncate text-sm">{{ $order->client->displayName() }}</p>
                        <p class="text-xs text-black/50">{{ $order->client->phone }} · {{ $order->price_type->emoji() }} {{ $order->price_type->label() }}</p>
                    </div>
                    <x-admin.status-badge :status="$order->status" />
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-black/5 pt-3 text-sm">
                    <span class="text-black/60">{{ trans_choice(':count product|:count products', $order->items_count) }}</span>
                    <span class="font-bold">{{ \App\Support\Money::format($order->total) }}</span>
                </div>
            </a>
        @empty
            <p class="admin-card px-4 py-10 text-center text-sm text-black/50">{{ __('No orders found.') }}</p>
        @endforelse
    </div>

    {{-- Tablets and desktops: table --}}
    <div class="admin-card hidden overflow-x-auto md:block">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-black/10">
                <tr><th class="admin-th">{{ __('Order') }}</th><th class="admin-th">{{ __('Customer') }}</th><th class="admin-th">{{ __('Items') }}</th><th class="admin-th">{{ __('Total') }}</th><th class="admin-th">{{ __('Status') }}</th><th class="admin-th">{{ __('Date') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @forelse ($orders as $order)
                    <tr class="cursor-pointer hover:bg-black/2" onclick="if (! event.target.closest('a')) location = '{{ route('admin.orders.show', $order) }}'">
                        <td class="px-4 py-3 font-semibold"><a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">{{ $order->number() }}</a></td>
                        <td class="px-4 py-3">{{ $order->client->displayName() }}<span class="block text-xs text-black/50">{{ $order->client->phone }} · {{ $order->price_type->emoji() }} {{ $order->price_type->label() }}</span></td>
                        <td class="px-4 py-3">{{ $order->items_count }}</td>
                        <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ \App\Support\Money::format($order->total) }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :status="$order->status" /></td>
                        <td class="px-4 py-3 whitespace-nowrap text-black/60">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-black/50">{{ __('No orders found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin.layout>
