<div x-data @keydown.escape.window="$wire.set('open', false)">
    <span class="admin-label">{{ $label }}</span>
    <div class="flex gap-2">
        <div class="relative min-w-0 flex-1">
            <input type="text" name="counterparty" wire:model.live.debounce.400ms="name" class="admin-input {{ $this->chosen ? 'pr-8' : '' }}" autocomplete="off">
            @if ($this->chosen)
                <x-admin.icon name="users" class="pointer-events-none absolute top-1/2 right-2.5 size-4 -translate-y-1/2 text-leaf" />
            @endif
        </div>
        <input type="hidden" name="telegram_client_id" value="{{ $clientId }}">
        <button type="button" wire:click="$set('open', true)" class="admin-btn-outline shrink-0"><x-admin.icon name="search" class="size-4" /> {{ __('Browse') }}</button>
    </div>
    @if ($this->chosen)
        <span class="mt-1 flex items-center gap-2 text-xs text-black/55">
            {{ __('Linked to customer') }}: {{ $this->chosen->fullName() }} · {{ $this->chosen->phone }} · {{ $this->chosen->type->label() }}
            <button type="button" wire:click="unlink" class="underline hover:text-ink">{{ __('Unlink') }}</button>
        </span>
    @endif
    @if ($error)<span class="mt-1 block text-xs font-medium text-red-600">{{ $error }}</span>@endif

    @if ($open)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-6" wire:click.self="$set('open', false)" role="dialog" aria-modal="true" aria-label="{{ __('Choose a customer') }}">
            <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-t-xl bg-white shadow-2xl sm:rounded-lg">
                <div class="flex items-center justify-between gap-3 border-b border-black/10 px-4 py-3 sm:px-5">
                    <h2 class="font-semibold">{{ __('Choose a customer') }}</h2>
                    <button type="button" wire:click="$set('open', false)" class="admin-icon-btn" aria-label="{{ __('Close') }}"><x-admin.icon name="close" class="size-4" /></button>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-b border-black/10 bg-black/[0.02] px-4 py-2.5 sm:px-5">
                    <div class="relative min-w-0 flex-1">
                        <x-admin.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-black/40" />
                        <input type="search" wire:model.live.debounce.250ms="search" placeholder="{{ __('Name, company, phone or VAT') }}" class="admin-input pl-9" autofocus @keydown.enter.prevent>
                    </div>
                    <div class="admin-chips">
                        @foreach (['' => __('All'), 'consumer' => __('Consumers'), 'reseller' => __('Resellers')] as $value => $chipLabel)
                            <button type="button" wire:click="$set('type', '{{ $value }}')" class="admin-chip" @if ($type === $value) aria-current="page" @endif>{{ $chipLabel }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    <table class="admin-table">
                        <thead class="sticky top-0 bg-white">
                            <tr><th class="admin-th">{{ __('Customer') }}</th><th class="admin-th">{{ __('Phone') }}</th><th class="admin-th">{{ __('Prices') }}</th><th class="admin-th text-right">{{ __('Orders') }}</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($this->clients as $client)
                                <tr wire:key="client-{{ $client->id }}" wire:click="choose({{ $client->id }})" @class(['cursor-pointer', 'bg-brand/[0.08]' => $client->id === $clientId])>
                                    <td class="admin-td">
                                        <span class="font-medium">{{ $client->displayName() }}</span>
                                        <span class="block text-xs text-black/50">{{ $client->company_name ? $client->fullName() : '' }}{{ $client->vat_number ? ($client->company_name ? ' · ' : '').__('VAT').' '.$client->vat_number : '' }}</span>
                                    </td>
                                    <td class="admin-td whitespace-nowrap">{{ $client->phone }}</td>
                                    <td class="admin-td"><x-admin.status-badge :status="$client->type" /></td>
                                    <td class="admin-td text-right tabular-nums">{{ $client->orders_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="admin-td py-10 text-center text-black/50">{{ __('No customers found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($this->clients->hasPages())
                    <div class="flex items-center justify-between gap-3 border-t border-black/10 px-4 py-2.5 text-[13px] sm:px-5">
                        <span class="text-black/55">{{ $this->clients->firstItem() }}–{{ $this->clients->lastItem() }} / {{ $this->clients->total() }}</span>
                        <span class="flex gap-1">
                            <button type="button" wire:click="previousPage('clientsPage')" @disabled($this->clients->onFirstPage()) class="admin-btn-outline h-8 px-2.5 disabled:opacity-40">←</button>
                            <button type="button" wire:click="nextPage('clientsPage')" @disabled(! $this->clients->hasMorePages()) class="admin-btn-outline h-8 px-2.5 disabled:opacity-40">→</button>
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
