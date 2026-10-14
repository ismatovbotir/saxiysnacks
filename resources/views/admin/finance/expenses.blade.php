<x-admin.layout :title="__('Expenses')">
    <x-admin.page-header :title="__('Expenses')" :subtitle="__('Spent in the period: :total', ['total' => \App\Support\Money::format($total)])" />

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-6">
        <div class="min-w-0">
            <div class="mb-4 flex flex-wrap items-end gap-2">
                <form method="GET" class="flex gap-2">
                    @foreach (request()->only(['from', 'to']) as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                    <select name="category" class="admin-input w-auto" onchange="this.form.submit()" aria-label="{{ __('Category') }}">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach (\App\Enums\ExpenseCategory::cases() as $case)
                            <option value="{{ $case->value }}" @selected($category === $case)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </form>
                <x-admin.period-filter :period="$period" />
            </div>

            <div class="admin-card overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr><th class="admin-th">{{ __('Date') }}</th><th class="admin-th">{{ __('Category') }}</th><th class="admin-th">{{ __('Note') }}</th><th class="admin-th text-right">{{ __('Amount') }}</th><th class="admin-th">{{ __('By') }}</th><th class="admin-th"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="admin-td whitespace-nowrap">{{ $expense->spent_on->format('d.m.Y') }}</td>
                                <td class="admin-td"><span class="admin-badge bg-black/[0.03] text-black/70 ring-black/10">{{ $expense->category->label() }}</span></td>
                                <td class="admin-td text-black/70">{{ $expense->note }}</td>
                                <td class="admin-td text-right font-semibold whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($expense->amount) }}</td>
                                <td class="admin-td whitespace-nowrap text-black/60">{{ $expense->user?->name ?? '—' }}</td>
                                <td class="admin-td text-right">
                                    <form method="POST" action="{{ route('admin.finance.expenses.destroy', $expense) }}" onsubmit="return confirm(@js(__('Delete this expense?')))">
                                        @csrf @method('DELETE')
                                        <button class="admin-icon-btn hover:text-red-700" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-admin.icon name="trash" class="size-4" /></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="admin-td py-10 text-center text-black/50">{{ __('No expenses in this period.') }}</td></tr>
                        @endforelse
                    </tbody>
                    @if ($expenses->isNotEmpty())
                        <tfoot class="border-t border-black/10 bg-black/[0.025] font-semibold">
                            <tr><td class="admin-td" colspan="3">{{ __('Total for the period') }}</td><td class="admin-td text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($total) }}</td><td colspan="2"></td></tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <div class="mt-4">{{ $expenses->links() }}</div>
        </div>

        <aside>
            <form method="POST" action="{{ route('admin.finance.expenses.store') }}" class="admin-card space-y-4 p-4 sm:p-5 lg:sticky lg:top-20">
                @csrf
                <h2 class="admin-card-title">{{ __('Add an expense') }}</h2>
                <x-admin.field :label="__('Category').' *'" name="category">
                    <select name="category" required class="admin-input">
                        @foreach (\App\Enums\ExpenseCategory::cases() as $case)
                            <option value="{{ $case->value }}" @selected(old('category') === $case->value)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
                <div class="grid grid-cols-2 gap-2">
                    <x-admin.field :label="__('Amount, so\'m').' *'" name="amount">
                        <input type="text" inputmode="numeric" name="amount" required value="{{ old('amount') }}" class="admin-input">
                    </x-admin.field>
                    <x-admin.field :label="__('Date')" name="spent_on">
                        <input type="date" name="spent_on" value="{{ old('spent_on', today()->toDateString()) }}" max="{{ today()->toDateString() }}" class="admin-input">
                    </x-admin.field>
                </div>
                <x-admin.field :label="__('Note')" name="note">
                    <input type="text" name="note" value="{{ old('note') }}" class="admin-input">
                </x-admin.field>
                <button class="admin-btn w-full">{{ __('Add expense') }}</button>
            </form>
        </aside>
    </div>
</x-admin.layout>
