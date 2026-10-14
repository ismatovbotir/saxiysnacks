<x-admin.layout :title="$client->displayName()">
    <x-admin.page-header :title="$client->displayName()" :subtitle="__('Customer since :date', ['date' => $client->created_at->format('d.m.Y')]).' · '.trans_choice(':count order|:count orders', $orders->count()).($orders->count() === 20 ? '+' : '')">
        <x-admin.status-badge :status="$client->type" />
        <x-admin.status-badge :status="$client->status" />
    </x-admin.page-header>

    <div class="grid gap-4 lg:grid-cols-3 lg:gap-6">
        <aside class="space-y-4 lg:order-last lg:space-y-6">
            <section class="admin-card p-4 sm:p-5">
                <h2 class="mb-3 text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ __('Actions') }}</h2>
                @include('admin.clients.partials.actions')
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="tel:{{ $client->phone }}" class="admin-btn-outline"><x-admin.icon name="phone" class="size-4" /> {{ __('Call') }}</a>
                    @if ($client->telegram_username)
                        <a href="https://t.me/{{ $client->telegram_username }}" target="_blank" rel="noopener" class="admin-btn-outline"><x-admin.icon name="send" class="size-4" /> {{ __('Message in Telegram') }} ↗</a>
                    @endif
                </div>
            </section>

            <section class="admin-card p-4 sm:p-5">
                <h2 class="mb-3 text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ __('Details') }}</h2>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm lg:grid-cols-1">
                    @foreach ([
                        __('Name') => $client->fullName(),
                        __('Phone') => $client->phone,
                        __('Company') => $client->company_name ?? '—',
                        __('VAT number') => $client->vat_number ?? '—',
                        __('Prices') => $client->type->label(),
                        'Telegram' => ($client->telegram_username ? '@'.$client->telegram_username.' · ' : '').'ID '.$client->telegram_id,
                        __('Language') => strtoupper($client->language_code),
                        __('Registered') => $client->created_at->format('d.m.Y H:i'),
                    ] as $label => $value)
                        <div class="min-w-0"><dt class="text-xs text-black/50">{{ $label }}</dt><dd class="font-medium break-words">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </section>
        </aside>

        <div class="space-y-4 lg:col-span-2 lg:space-y-6">
            <section class="admin-card p-4 sm:p-5">
                <h2 class="mb-3 text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ __('Delivery location') }} <span class="text-sm font-normal text-black/50">{{ $client->location_updated_at ? '· '.__('updated :date', ['date' => $client->location_updated_at->format('d.m.Y')]) : '' }}</span></h2>
                <x-admin.location :latitude="$client->latitude" :longitude="$client->longitude" :address="$client->address" />
            </section>

            <section class="admin-card">
                <h2 class="admin-card-header admin-card-title">{{ __('Orders') }}</h2>
                <ul class="divide-y divide-black/5">
                    @forelse ($orders as $order)
                        <li>
                            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-black/2 sm:px-5">
                                <span class="min-w-0"><span class="font-semibold">{{ $order->number() }}</span> <span class="block text-sm text-black/60 sm:inline">{{ $order->created_at->format('d.m.Y H:i') }}</span></span>
                                <span class="flex shrink-0 flex-col items-end gap-1 sm:flex-row sm:items-center sm:gap-3"><span class="font-semibold">{{ \App\Support\Money::format($order->total) }}</span> <x-admin.status-badge :status="$order->status" /></span>
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-8 text-center text-sm text-black/50">{{ __('No orders yet.') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-admin.layout>
