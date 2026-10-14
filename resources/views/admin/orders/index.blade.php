<x-admin.layout title="Orders">
    <x-admin.page-header title="Orders" />

    <div class="mb-4 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('admin.orders.index') }}" @class(['rounded-full px-3 py-1.5 border border-black/10', 'bg-ink text-white' => $status === null])>All</a>
        @foreach (\App\Enums\OrderStatus::cases() as $case)
            <a href="{{ route('admin.orders.index', ['status' => $case->value]) }}" @class(['rounded-full px-3 py-1.5 border border-black/10', 'bg-ink text-white' => $status === $case])>{{ $case->label() }}</a>
        @endforeach
    </div>

    <div class="admin-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-black/10 text-black/60">
                <tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Items</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Date</th></tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @forelse ($orders as $order)
                    <tr class="hover:bg-black/2">
                        <td class="px-4 py-3 font-semibold"><a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">{{ $order->number() }}</a></td>
                        <td class="px-4 py-3">{{ $order->client->displayName() }}<span class="block text-xs text-black/50">{{ $order->client->phone }} · {{ $order->price_type->emoji() }} {{ $order->price_type->label() }}</span></td>
                        <td class="px-4 py-3">{{ $order->items_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap font-semibold">{{ \App\Support\Money::format($order->total) }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :status="$order->status" /></td>
                        <td class="px-4 py-3 whitespace-nowrap text-black/60">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-black/50">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin.layout>
