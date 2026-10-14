<x-admin.layout :title="__('Customers')">
    <x-admin.page-header :title="__('Customers')" :subtitle="trans_choice(':count customer|:count customers', $clients->total())" />

    <div class="admin-filterbar">
        <nav class="admin-chips" aria-label="{{ __('Filter customers') }}">
            <a href="{{ route('admin.clients.index', request()->only('search')) }}" class="admin-chip" @if (! $type && ! $status) aria-current="page" @endif>{{ __('All') }}</a>
            @foreach (\App\Enums\ClientType::cases() as $case)
                <a href="{{ route('admin.clients.index', [...request()->only('search', 'status'), 'type' => $case->value]) }}" class="admin-chip" @if ($type === $case) aria-current="page" @endif>{{ $case->emoji() }} {{ __($case->name.'s') }}</a>
            @endforeach
            @foreach (\App\Enums\ClientStatus::cases() as $case)
                <a href="{{ route('admin.clients.index', [...request()->only('search', 'type'), 'status' => $case->value]) }}" class="admin-chip" @if ($status === $case) aria-current="page" @endif>{{ $case->emoji() }} {{ $case->label() }}</a>
            @endforeach
        </nav>
        <form method="GET" class="flex w-full gap-2 sm:ml-auto sm:w-auto">
            @if ($type)<input type="hidden" name="type" value="{{ $type->value }}">@endif
            @if ($status)<input type="hidden" name="status" value="{{ $status->value }}">@endif
            <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Name, phone, company, VAT') }}" class="admin-input sm:w-64">
            <button class="admin-btn-outline">{{ __('Search') }}</button>
        </form>
    </div>

    <div class="space-y-2 md:hidden">
        @forelse ($clients as $client)
            <div class="admin-card p-4">
                <a href="{{ route('admin.clients.show', $client) }}" class="flex items-start justify-between gap-3">
                    <span class="min-w-0">
                        <span class="block truncate font-semibold">{{ $client->fullName() }}</span>
                        <span class="block truncate text-sm text-black/60">{{ $client->company_name ?? __('Private customer') }}{{ $client->vat_number ? ' · '.__('VAT').' '.$client->vat_number : '' }}</span>
                        <span class="block text-xs text-black/50">{{ $client->phone }} · {{ trans_choice(':count order|:count orders', $client->orders_count) }}</span>
                    </span>
                    <span class="flex shrink-0 flex-col items-end gap-1">
                        <x-admin.status-badge :status="$client->type" />
                        @unless ($client->isActive())<x-admin.status-badge :status="$client->status" />@endunless
                    </span>
                </a>
                <div class="mt-3 border-t border-black/5 pt-3">@include('admin.clients.partials.actions')</div>
            </div>
        @empty
            <p class="admin-card px-4 py-10 text-center text-sm text-black/50">{{ __('No customers found.') }}</p>
        @endforelse
    </div>

    <div class="admin-card hidden overflow-x-auto md:block">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-black/10">
                <tr><th class="admin-th">{{ __('Customer') }}</th><th class="admin-th">{{ __('Company') }}</th><th class="admin-th">{{ __('Prices') }}</th><th class="admin-th">{{ __('Orders') }}</th><th class="admin-th">{{ __('Status') }}</th><th class="admin-th">{{ __('Registered') }}</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @forelse ($clients as $client)
                    <tr class="hover:bg-black/2">
                        <td class="px-4 py-3"><a href="{{ route('admin.clients.show', $client) }}" class="font-semibold hover:underline">{{ $client->fullName() }}</a><span class="block text-xs text-black/50">{{ $client->phone }}{{ $client->telegram_username ? ' · @'.$client->telegram_username : '' }}</span></td>
                        <td class="px-4 py-3">{{ $client->company_name ?? '—' }}@if ($client->vat_number)<span class="block font-mono text-xs text-black/50">{{ __('VAT') }} {{ $client->vat_number }}</span>@endif</td>
                        <td class="px-4 py-3"><x-admin.status-badge :status="$client->type" /></td>
                        <td class="px-4 py-3">{{ $client->orders_count }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :status="$client->status" /></td>
                        <td class="px-4 py-3 whitespace-nowrap text-black/60">{{ $client->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-3 text-right">@include('admin.clients.partials.actions')</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-black/50">{{ __('No customers found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</x-admin.layout>
