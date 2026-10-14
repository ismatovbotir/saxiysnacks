<x-admin.layout :title="$type->pluralLabel()">
    <x-admin.page-header :title="$type->pluralLabel()" :subtitle="$type->hint()">
        <a href="{{ route('admin.stock.'.$type->slug().'.create') }}" class="admin-btn"><x-admin.icon name="plus" class="size-4" /> {{ $type->label() }}</a>
    </x-admin.page-header>

    <div class="admin-filterbar">
        <nav class="admin-chips" aria-label="{{ __('Status') }}">
            <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" class="admin-chip" @if ($status === null) aria-current="page" @endif>{{ __('All') }}</a>
            @foreach (\App\Enums\StockDocumentStatus::cases() as $case)
                <a href="{{ request()->fullUrlWithQuery(['status' => $case->value, 'page' => null]) }}" class="admin-chip" @if ($status === $case) aria-current="page" @endif>{{ $case->label() }}</a>
            @endforeach
        </nav>
        <form method="GET" class="flex w-full flex-wrap items-center gap-2 sm:ml-auto sm:w-auto">
            @if ($status)<input type="hidden" name="status" value="{{ $status->value }}">@endif
            <input type="date" name="from" value="{{ $period?->from->toDateString() }}" class="admin-input w-auto" aria-label="{{ __('From') }}">
            <span class="text-black/40">—</span>
            <input type="date" name="to" value="{{ $period?->to->toDateString() }}" class="admin-input w-auto" aria-label="{{ __('To') }}">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Number, counterparty, comment') }}" class="admin-input min-w-0 flex-1 sm:w-56 sm:flex-none">
            <button class="admin-btn-outline">{{ __('Apply') }}</button>
        </form>
    </div>

    <div class="admin-card overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="admin-th">№</th><th class="admin-th">{{ __('Date') }}</th><th class="admin-th">{{ $type->counterpartyLabel() }}</th>
                    <th class="admin-th text-right">{{ __('Pieces') }}</th>
                    @if ($type->hasPrices())<th class="admin-th text-right">{{ __('Amount') }}</th>@endif
                    <th class="admin-th">{{ __('Status') }}</th><th class="admin-th">{{ __('Comment') }}</th><th class="admin-th">{{ __('Author') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr class="cursor-pointer" onclick="if (! event.target.closest('a')) location = '{{ route('admin.stock.documents.show', $document) }}'">
                        <td class="admin-td font-mono font-semibold whitespace-nowrap"><a href="{{ route('admin.stock.documents.show', $document) }}" class="hover:underline">{{ $document->number }}</a></td>
                        <td class="admin-td whitespace-nowrap">{{ $document->document_date->format('d.m.Y') }}</td>
                        <td class="admin-td">{{ $document->counterparty ?? '—' }}@if ($document->order)<span class="block text-xs text-black/50">{{ __('Order') }} {{ $document->order->number() }}</span>@endif</td>
                        <td @class(['admin-td text-right tabular-nums', 'text-red-700' => (int) $document->pieces_total < 0])>{{ number_format((int) $document->pieces_total, 0, '.', ' ') }}</td>
                        @if ($type->hasPrices())<td class="admin-td text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($document->total) }}</td>@endif
                        <td class="admin-td"><x-admin.document-status :status="$document->status" /></td>
                        <td class="admin-td max-w-56 truncate text-black/60">{{ $document->note }}</td>
                        <td class="admin-td whitespace-nowrap text-black/60">{{ $document->creator?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-td py-10 text-center text-black/50">{{ __('No documents yet.') }} <a href="{{ route('admin.stock.'.$type->slug().'.create') }}" class="font-medium text-ink underline">{{ __('Create the first one') }}</a></td></tr>
                @endforelse
            </tbody>
            @if ($documents->isNotEmpty() && $type->hasPrices())
                <tfoot class="border-t border-black/10 bg-black/[0.025] font-semibold">
                    <tr><td class="admin-td" colspan="4">{{ __('Posted total') }} · {{ trans_choice(':count document|:count documents', $totals['count']) }}</td><td class="admin-td text-right whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($totals['amount']) }}</td><td colspan="3"></td></tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="mt-4">{{ $documents->links() }}</div>
</x-admin.layout>
