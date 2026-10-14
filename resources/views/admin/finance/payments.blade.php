<x-admin.layout :title="__('Payments')">
    <x-admin.page-header :title="__('Payments')" :subtitle="__('Money received in the period: :total', ['total' => \App\Support\Money::format($total)])">
        <a href="{{ route('admin.finance.debts') }}" class="admin-btn-outline"><x-admin.icon name="alert" class="size-4" /> {{ __('Debts') }}</a>
    </x-admin.page-header>

    <div class="admin-filterbar">
        <nav class="admin-chips" aria-label="{{ __('Filter by method') }}">
            <a href="{{ request()->fullUrlWithQuery(['method' => null, 'page' => null]) }}" class="admin-chip" @if ($method === null) aria-current="page" @endif>{{ __('All') }}</a>
            @foreach (\App\Enums\PaymentMethod::cases() as $case)
                <a href="{{ request()->fullUrlWithQuery(['method' => $case->value, 'page' => null]) }}" class="admin-chip" @if ($method === $case) aria-current="page" @endif>{{ $case->label() }}</a>
            @endforeach
        </nav>
        <x-admin.period-filter :period="$period" class="sm:ml-auto" />
    </div>

    <p class="mb-3 text-[13px] text-black/55">{{ __('Payments are recorded on the order page, in the "Payments" block.') }}</p>

    <div class="admin-card overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr><th class="admin-th">{{ __('Date') }}</th><th class="admin-th">{{ __('Order') }}</th><th class="admin-th">{{ __('Customer') }}</th><th class="admin-th">{{ __('Method') }}</th><th class="admin-th text-right">{{ __('Amount') }}</th><th class="admin-th">{{ __('Note') }}</th><th class="admin-th">{{ __('By') }}</th><th class="admin-th"></th></tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="admin-td whitespace-nowrap">{{ $payment->paid_on->format('d.m.Y') }}</td>
                        <td class="admin-td whitespace-nowrap">@if ($payment->order)<a href="{{ route('admin.orders.show', $payment->order) }}" class="font-medium hover:underline">{{ $payment->order->number() }}</a>@else—@endif</td>
                        <td class="admin-td">{{ $payment->order?->customerName() ?? $payment->client?->displayName() ?? '—' }}</td>
                        <td class="admin-td"><span class="admin-badge bg-black/[0.03] text-black/70 ring-black/10">{{ $payment->method->label() }}</span></td>
                        <td class="admin-td text-right font-semibold whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($payment->amount) }}</td>
                        <td class="admin-td text-black/70">{{ $payment->note }}</td>
                        <td class="admin-td whitespace-nowrap text-black/60">{{ $payment->user?->name ?? '—' }}</td>
                        <td class="admin-td text-right">
                            <form method="POST" action="{{ route('admin.finance.payments.destroy', $payment) }}" onsubmit="return confirm(@js(__('Delete this payment?')))">
                                @csrf @method('DELETE')
                                <button class="admin-icon-btn hover:text-red-700" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-admin.icon name="trash" class="size-4" /></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-td py-10 text-center text-black/50">{{ __('No payments in this period.') }}</td></tr>
                @endforelse
            </tbody>
            @if ($payments->isNotEmpty())
                <tfoot class="border-t border-black/10 bg-black/[0.025] font-semibold">
                    <tr><td class="admin-td" colspan="4">{{ __('Total for the period') }}</td><td class="admin-td text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($total) }}</td><td colspan="3"></td></tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</x-admin.layout>
