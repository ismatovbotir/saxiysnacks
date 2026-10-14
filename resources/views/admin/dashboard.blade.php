<x-admin.layout :title="__('Dashboard')">
    <x-admin.page-header :title="__('Dashboard')" :subtitle="__('Hello, :name — here is what needs attention.', ['name' => auth()->user()->name])">
        @can('access-products')
            <a href="{{ route('admin.products.create') }}" class="admin-btn-outline"><x-admin.icon name="plus" class="size-4" /> {{ __('Product') }}</a>
        @endcan
        @can('access-stock')
            <a href="{{ route('admin.stock.index') }}" class="admin-btn-outline"><x-admin.icon name="warehouse" class="size-4" /> {{ __('Stock') }}</a>
        @endcan
        @can('access-report')
            <a href="{{ route('admin.finance.report') }}" class="admin-btn-outline"><x-admin.icon name="chart" class="size-4" /> {{ __('Report') }}</a>
        @endcan
        @can('access-orders')
            <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="admin-btn"><x-admin.icon name="receipt" class="size-4" /> {{ __('New orders') }}</a>
        @endcan
    </x-admin.page-header>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-3 2xl:grid-cols-6">
        @foreach ([
            [__('New orders'), $newOrders, __('to confirm'), route('admin.orders.index', ['status' => 'new']), $newOrders > 0 ? 'attention' : null, 'receipt', 'orders'],
            [__('Orders today'), $ordersToday, __('since 00:00'), route('admin.orders.index'), null, 'receipt', 'orders'],
            [__('This week'), \App\Support\Money::format($weekRevenue), trans_choice(':count order|:count orders', $weekOrders), route('admin.orders.index'), null, 'chart', 'orders'],
            [__('Active customers'), $customers, __('consumers + resellers'), route('admin.clients.index', ['status' => 'active']), null, 'users', 'customers'],
            [__('Resellers'), $resellers, __('reseller prices'), route('admin.clients.index', ['type' => 'reseller']), null, 'users', 'customers'],
            [__('Without a price'), $unpricedProducts, __('cannot be ordered'), route('admin.products.index'), $unpricedProducts > 0 ? 'warning' : null, 'box', 'products'],
        ] as [$label, $value, $hint, $url, $tone, $icon, $area])
            @cannot('access-'.$area)
                @continue
            @endcannot
            <a href="{{ $url }}" @class([
                'admin-card group relative flex flex-col overflow-hidden p-4 transition hover:border-black/25 hover:shadow-sm',
                'before:absolute before:inset-x-0 before:top-0 before:h-0.5 before:bg-brand' => $tone === 'attention',
                'before:absolute before:inset-x-0 before:top-0 before:h-0.5 before:bg-amber-500' => $tone === 'warning',
            ])>
                <span class="flex items-center justify-between gap-2">
                    <span class="truncate text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ $label }}</span>
                    <x-admin.icon :name="$icon" @class(['size-4', 'text-brand' => $tone === 'attention', 'text-amber-600' => $tone === 'warning', 'text-black/30' => $tone === null]) />
                </span>
                <span class="mt-2 truncate text-2xl font-semibold tracking-tight tabular-nums">{{ $value }}</span>
                <span class="mt-auto pt-1 text-xs text-black/50">{{ $hint }}</span>
            </a>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2 lg:gap-6">
        @can('access-orders')
        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">{{ __('Recent orders') }}</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-[13px] font-medium text-black/55 hover:text-ink">{{ __('All') }} →</a>
            </div>
            <ul class="divide-y divide-black/[0.06]">
                @forelse ($recentOrders as $order)
                    <li>
                        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-brand/[0.04] sm:px-5">
                            <span class="min-w-0">
                                <span class="font-semibold">{{ $order->number() }}</span>
                                <span class="block truncate text-sm text-black/60">{{ $order->customerName() }} · {{ $order->created_at->format('d.m H:i') }}</span>
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
        @endcan

        @can('access-customers')
        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">{{ __('Newest customers') }}</h2>
                <a href="{{ route('admin.clients.index') }}" class="text-[13px] font-medium text-black/55 hover:text-ink">{{ __('All') }} →</a>
            </div>
            <ul class="divide-y divide-black/[0.06]">
                @forelse ($recentClients as $client)
                    <li class="flex flex-col gap-2 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
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
        @endcan
    </div>
</x-admin.layout>
