<x-admin.layout :title="__('Dashboard')">
    <x-admin.page-header :title="__('Dashboard')" :subtitle="__('Hello, :name — here is what needs attention.', ['name' => auth()->user()->name])">
        <a href="{{ route('admin.products.create') }}" class="admin-btn-outline"><x-admin.icon name="plus" class="size-4" /> {{ __('Product') }}</a>
        <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="admin-btn"><x-admin.icon name="receipt" class="size-4" /> {{ __('New orders') }}</a>
    </x-admin.page-header>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ([
            [__('New orders'), $newOrders, __('to confirm'), route('admin.orders.index', ['status' => 'new']), $newOrders > 0 ? 'attention' : null],
            [__('Orders today'), $ordersToday, __('since 00:00'), route('admin.orders.index'), null],
            [__('This week'), \App\Support\Money::format($weekRevenue), trans_choice(':count order|:count orders', $weekOrders), route('admin.orders.index'), null],
            [__('Active customers'), $customers, __('consumers + resellers'), route('admin.clients.index', ['status' => 'active']), null],
            [__('Resellers'), $resellers, __('reseller prices'), route('admin.clients.index', ['type' => 'reseller']), null],
            [__('Without a price'), $unpricedProducts, __('cannot be ordered'), route('admin.products.index'), $unpricedProducts > 0 ? 'warning' : null],
        ] as [$label, $value, $hint, $url, $tone])
            <a href="{{ $url }}" @class([
                'admin-card flex flex-col p-4 transition-colors hover:border-brand',
                'border-brand bg-brand/10' => $tone === 'attention',
                'border-amber-300 bg-amber-50' => $tone === 'warning',
            ])>
                <span class="text-xs font-medium text-black/60 sm:text-sm">{{ $label }}</span>
                <span class="mt-1 truncate text-2xl font-bold sm:text-3xl">{{ $value }}</span>
                <span class="mt-auto pt-1 text-xs text-black/50">{{ $hint }}</span>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="admin-card">
            <div class="flex items-center justify-between border-b border-black/10 px-4 py-3 sm:px-5">
                <h2 class="font-semibold">{{ __('Recent orders') }}</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-black/60 hover:text-ink">{{ __('All') }} →</a>
            </div>
            <ul class="divide-y divide-black/5">
                @forelse ($recentOrders as $order)
                    <li>
                        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-black/2 sm:px-5">
                            <span class="min-w-0">
                                <span class="font-semibold">{{ $order->number() }}</span>
                                <span class="block truncate text-sm text-black/60">{{ $order->client->displayName() }} · {{ $order->created_at->format('d.m H:i') }}</span>
                            </span>
                            <span class="shrink-0 text-right">
                                <span class="block text-sm font-semibold">{{ \App\Support\Money::format($order->total) }}</span>
                                <x-admin.status-badge :status="$order->status" />
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-black/50">{{ __('No orders yet. They appear here as soon as a customer orders in the Telegram app.') }}</li>
                @endforelse
            </ul>
        </section>

        <section class="admin-card">
            <div class="flex items-center justify-between border-b border-black/10 px-4 py-3 sm:px-5">
                <h2 class="font-semibold">{{ __('Newest customers') }}</h2>
                <a href="{{ route('admin.clients.index') }}" class="text-sm font-medium text-black/60 hover:text-ink">{{ __('All') }} →</a>
            </div>
            <ul class="divide-y divide-black/5">
                @forelse ($recentClients as $client)
                    <li class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <a href="{{ route('admin.clients.show', $client) }}" class="min-w-0 hover:underline">
                            <span class="font-semibold">{{ $client->displayName() }}</span>
                            <span class="block truncate text-sm text-black/60">{{ $client->fullName() }} · {{ $client->phone }}</span>
                        </a>
                        @include('admin.clients.partials.actions')
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-black/50">{{ __('No customers yet.') }}</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-admin.layout>
