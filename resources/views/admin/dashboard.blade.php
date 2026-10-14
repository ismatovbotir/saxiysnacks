<x-admin.layout title="Dashboard">
    <x-admin.page-header title="Dashboard" />

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['New orders', $newOrders, route('admin.orders.index', ['status' => 'new'])],
            ['Active customers', $customers, route('admin.clients.index', ['status' => 'active'])],
            ['Resellers', $resellers, route('admin.clients.index', ['type' => 'reseller'])],
            ['Products without a price', $unpricedProducts, route('admin.products.index')],
        ] as [$label, $value, $url])
            <a href="{{ $url }}" class="admin-card p-5 hover:border-brand">
                <div class="text-sm text-black/60">{{ $label }}</div>
                <div class="mt-1 text-3xl font-bold">{{ $value }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="admin-card">
            <h2 class="border-b border-black/10 px-5 py-3 font-semibold">Recent orders</h2>
            <ul class="divide-y divide-black/5">
                @forelse ($recentOrders as $order)
                    <li>
                        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-black/2">
                            <span>
                                <span class="font-semibold">{{ $order->number() }}</span>
                                <span class="block text-sm text-black/60">{{ $order->client->displayName() }} · {{ $order->created_at->format('d.m.Y H:i') }}</span>
                            </span>
                            <span class="text-right">
                                <span class="block text-sm font-semibold">{{ \App\Support\Money::format($order->total) }}</span>
                                <x-admin.status-badge :status="$order->status" />
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-6 text-sm text-black/50">No orders yet.</li>
                @endforelse
            </ul>
        </section>

        <section class="admin-card">
            <h2 class="border-b border-black/10 px-5 py-3 font-semibold">Newest customers</h2>
            <ul class="divide-y divide-black/5">
                @forelse ($recentClients as $client)
                    <li class="flex items-center justify-between gap-3 px-5 py-3">
                        <a href="{{ route('admin.clients.show', $client) }}" class="hover:underline">
                            <span class="font-semibold">{{ $client->displayName() }}</span>
                            <span class="block text-sm text-black/60">{{ $client->fullName() }} · {{ $client->phone }}</span>
                        </a>
                        @include('admin.clients.partials.actions')
                    </li>
                @empty
                    <li class="px-5 py-6 text-sm text-black/50">No customers yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-admin.layout>
