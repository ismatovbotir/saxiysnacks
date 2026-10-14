<x-admin.layout :title="__('Report')">
    <x-admin.page-header :title="__('Finance report')" :subtitle="$period->from->format('d.m.Y').' — '.$period->to->format('d.m.Y')" />

    <div class="admin-filterbar"><x-admin.period-filter :period="$period" /></div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            [__('Sales'), \App\Support\Money::format($sales['total']), trans_choice(':count order|:count orders', $sales['count']), null],
            [__('Received'), \App\Support\Money::format($received), __('payments in the period'), null],
            [__('Expenses'), \App\Support\Money::format($spent), __('spent in the period'), null],
            [__('Net cash flow'), ($net < 0 ? '−' : '').\App\Support\Money::format(abs($net)), __('received − expenses'), $net < 0 ? 'negative' : 'positive'],
        ] as [$label, $value, $hint, $tone])
            <div @class(['admin-card relative overflow-hidden p-4', 'before:absolute before:inset-x-0 before:top-0 before:h-0.5 before:bg-leaf' => $tone === 'positive', 'before:absolute before:inset-x-0 before:top-0 before:h-0.5 before:bg-red-600' => $tone === 'negative'])>
                <span class="block text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ $label }}</span>
                <span @class(['mt-2 block truncate text-xl font-semibold tabular-nums sm:text-2xl', 'text-red-700' => $tone === 'negative'])>{{ $value }}</span>
                <span class="mt-1 block text-xs text-black/50">{{ $hint }}</span>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-3 lg:gap-6">
        @foreach ([
            [__('Sales by channel'), $sales['bySource']->put(__('of which shipped'), $sales['shipped']), $sales['total'], route('admin.orders.index')],
            [__('Received by method'), $receivedByMethod, $received, route('admin.finance.payments.index', $period->query())],
            [__('Expenses by category'), $spentByCategory, $spent, route('admin.finance.expenses.index', $period->query())],
        ] as [$title, $rows, $sum, $url])
            <section class="admin-card overflow-hidden">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">{{ $title }}</h2>
                    <a href="{{ $url }}" class="text-[13px] font-medium text-black/55 hover:text-ink">{{ __('Details') }} →</a>
                </div>
                <table class="admin-table">
                    <tbody>
                        @forelse ($rows as $label => $amount)
                            <tr>
                                <td class="admin-td">
                                    {{ $label }}
                                    @if ($sum > 0)
                                        <span class="mt-1 block h-1 rounded-full bg-black/[0.06]"><span class="block h-1 rounded-full bg-brand" style="width: {{ min(100, round($amount / $sum * 100)) }}%"></span></span>
                                    @endif
                                </td>
                                <td class="admin-td w-1 text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="admin-td py-8 text-center text-black/50">{{ __('Nothing in this period.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @endforeach
    </div>
</x-admin.layout>
