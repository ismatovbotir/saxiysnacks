<x-admin.layout :title="__('Debts')">
    <x-admin.page-header :title="__('Debts')" :subtitle="__('Owed for shipped orders: :total', ['total' => \App\Support\Money::format($totalDue)])" />

    <div class="grid gap-4 lg:grid-cols-2 lg:gap-6">
        <section class="admin-card overflow-hidden">
            <div class="admin-card-header">
                <h2 class="admin-card-title">{{ __('By customer') }}</h2>
                <span class="text-xs text-black/50">{{ __('Shipped orders − payments') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th class="admin-th">{{ __('Customer') }}</th><th class="admin-th text-right">{{ __('Billed') }}</th><th class="admin-th text-right">{{ __('Paid') }}</th><th class="admin-th text-right">{{ __('Balance') }}</th></tr></thead>
                    <tbody>
                        @forelse ($customers as $client)
                            <tr>
                                <td class="admin-td"><a href="{{ route('admin.clients.show', $client) }}" class="font-medium hover:underline">{{ $client->displayName() }}</a><span class="block text-xs text-black/50">{{ $client->phone }} · {{ $client->type->label() }}</span></td>
                                <td class="admin-td text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format((int) $client->billed_total) }}</td>
                                <td class="admin-td text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format((int) $client->paid_total) }}</td>
                                <td @class(['admin-td text-right font-semibold whitespace-nowrap tabular-nums', 'text-red-700' => $client->balance > 0, 'text-leaf-dark' => $client->balance < 0])>
                                    {{ \App\Support\Money::format(abs($client->balance)) }}
                                    <span class="block text-[11px] font-normal text-black/50">{{ $client->balance > 0 ? __('owes') : __('prepaid') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="admin-td py-10 text-center text-black/50">{{ __('No open balances.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="admin-card-header">
                <h2 class="admin-card-title">{{ __('Unpaid orders') }}</h2>
                <span class="text-xs text-black/50">{{ trans_choice(':count order|:count orders', $unpaidOrders->count()) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th class="admin-th">{{ __('Order') }}</th><th class="admin-th">{{ __('Customer') }}</th><th class="admin-th text-right">{{ __('Total') }}</th><th class="admin-th text-right">{{ __('Due') }}</th></tr></thead>
                    <tbody>
                        @forelse ($unpaidOrders as $order)
                            <tr>
                                <td class="admin-td whitespace-nowrap"><a href="{{ route('admin.orders.show', $order) }}" class="font-medium hover:underline">{{ $order->number() }}</a><span class="block text-xs text-black/50">{{ $order->created_at->format('d.m.Y') }} · {{ $order->created_at->diffForHumans() }}</span></td>
                                <td class="admin-td">{{ $order->customerName() }}<span class="block text-xs text-black/50">{{ $order->customerPhone() }}</span></td>
                                <td class="admin-td text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($order->total) }}</td>
                                <td class="admin-td text-right font-semibold whitespace-nowrap text-red-700 tabular-nums">{{ \App\Support\Money::format($order->amountDue()) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="admin-td py-10 text-center text-black/50">{{ __('All shipped orders are paid.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin.layout>
