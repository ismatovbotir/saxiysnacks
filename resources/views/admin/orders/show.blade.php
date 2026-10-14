@php
    $nextStatuses = app(\App\Services\Telegram\AdminChatMessages::class)->nextOrderStatuses($order->status);
    $client = $order->client;
@endphp

<x-admin.layout :title="__('Order :number', ['number' => $order->number()])">
    <x-admin.page-header :title="__('Order :number', ['number' => $order->number()])" :subtitle="__('Placed :date', ['date' => $order->created_at->format('d.m.Y H:i')]).' · '.trans_choice(':count product|:count products', $order->items->count())">
        <x-admin.status-badge :status="$order->status" />
    </x-admin.page-header>

    <div class="grid gap-4 lg:grid-cols-3 lg:gap-6">
        <div class="space-y-4 lg:col-span-2 lg:space-y-6">
            <section class="admin-card">
                <h2 class="admin-card-header admin-card-title">{{ __('Items') }}</h2>

                <ul class="divide-y divide-black/5 md:hidden">
                    @foreach ($order->items as $item)
                        <li class="flex items-start justify-between gap-3 px-4 py-3 text-sm">
                            <span class="min-w-0">
                                <span class="block font-semibold">{{ $item->product_name }}</span>
                                <span class="block text-xs text-black/50">{{ $item->weight_grams }} {{ __('g') }}{{ $item->box_quantity ? ' · '.__(':count pcs per package', ['count' => $item->box_quantity]) : '' }}</span>
                                <span class="block text-black/70">{{ $item->quantity }} {{ $order->unit()->label() }} × {{ \App\Support\Money::format($item->unit_price) }}</span>
                            </span>
                            <span class="shrink-0 font-semibold">{{ \App\Support\Money::format($item->line_total) }}</span>
                        </li>
                    @endforeach
                </ul>

                <table class="hidden w-full text-left text-sm md:table">
                    <thead>
                        <tr><th class="admin-th">{{ __('Product') }}</th><th class="admin-th">{{ __('Quantity') }}</th><th class="admin-th">{{ __('Unit price') }}</th><th class="admin-th text-right">{{ __('Sum') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="admin-td">{{ $item->product_name }}<span class="block text-xs text-black/50">{{ $item->weight_grams }} {{ __('g') }}{{ $item->box_quantity ? ' · '.__(':count pcs per package', ['count' => $item->box_quantity]) : '' }}</span></td>
                                <td class="admin-td whitespace-nowrap">{{ $item->quantity }} {{ $order->unit()->label() }}@if ($order->unit() === \App\Enums\OrderUnit::Package && $item->pieces())<span class="block text-xs text-black/50">{{ __(':count pcs', ['count' => $item->pieces()]) }}</span>@endif</td>
                                <td class="admin-td whitespace-nowrap">{{ \App\Support\Money::format($item->unit_price) }}</td>
                                <td class="admin-td text-right whitespace-nowrap">{{ \App\Support\Money::format($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex items-center justify-between border-t border-black/10 px-4 py-3 font-bold sm:px-5">
                    <span>{{ __('Total') }}</span><span>{{ \App\Support\Money::format($order->total) }}</span>
                </div>
                @if ($order->comment)
                    <div class="border-t border-black/10 bg-brand/5 px-4 py-3 text-sm sm:px-5"><span class="block text-xs font-semibold text-black/50 uppercase">{{ __('Customer comment') }}</span>{{ $order->comment }}</div>
                @endif
            </section>

            <section class="admin-card overflow-hidden">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">{{ __('Payments') }}</h2>
                    <span class="text-[13px]">{{ __('Paid') }} <b class="tabular-nums">{{ \App\Support\Money::format($order->payments->sum('amount')) }}</b>
                        · {{ __('Due') }} <b @class(['tabular-nums', 'text-red-700' => $order->amountDue() > 0])>{{ \App\Support\Money::format($order->amountDue()) }}</b></span>
                </div>
                @if ($order->payments->isNotEmpty())
                    <ul class="divide-y divide-black/[0.06] text-sm">
                        @foreach ($order->payments as $payment)
                            <li class="flex items-center justify-between gap-3 px-4 py-2.5 sm:px-5">
                                <span>{{ $payment->paid_on->format('d.m.Y') }} · {{ $payment->method->label() }}@if ($payment->note) <span class="text-black/50">· {{ $payment->note }}</span>@endif</span>
                                <b class="tabular-nums">{{ \App\Support\Money::format($payment->amount) }}</b>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @can('access-payments')
                    @if ($order->amountDue() > 0)
                        <form method="POST" action="{{ route('admin.finance.payments.store', $order) }}" class="grid gap-2 border-t border-black/10 bg-black/[0.02] p-4 sm:grid-cols-[minmax(0,1fr)_9rem_10rem_auto] sm:items-end sm:px-5">
                            @csrf
                            <x-admin.field :label="__('Amount, so\'m')" name="amount">
                                <input type="text" inputmode="numeric" name="amount" required value="{{ old('amount', $order->amountDue()) }}" class="admin-input">
                            </x-admin.field>
                            <x-admin.field :label="__('Method')" name="method">
                                <select name="method" class="admin-input">
                                    @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                        <option value="{{ $method->value }}" @selected(old('method') === $method->value)>{{ $method->label() }}</option>
                                    @endforeach
                                </select>
                            </x-admin.field>
                            <x-admin.field :label="__('Date')" name="paid_on">
                                <input type="date" name="paid_on" value="{{ old('paid_on', today()->toDateString()) }}" max="{{ today()->toDateString() }}" class="admin-input">
                            </x-admin.field>
                            <button class="admin-btn">{{ __('Record payment') }}</button>
                        </form>
                    @endif
                @endcan
            </section>

            @if ($order->hasDeliveryLocation() || $order->delivery_address)
                <section class="admin-card p-4 sm:p-5">
                    <h2 class="mb-3 text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ __('Delivery') }}</h2>
                    <x-admin.location :latitude="$order->delivery_latitude" :longitude="$order->delivery_longitude" :address="$order->delivery_address" />
                </section>
            @endif
        </div>

        <aside class="space-y-4 lg:space-y-6">
            <section class="admin-card p-4 sm:p-5">
                <h2 class="mb-3 text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ __('Status') }}</h2>
                @if ($nextStatuses)
                    <div class="hidden flex-wrap gap-2 lg:flex">
                        @foreach ($nextStatuses as $next)
                            @include('admin.orders.partials.status-button')
                        @endforeach
                    </div>
                @endif
                @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                @if (auth()->user()->role === \App\Enums\UserRole::Owner)
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 flex gap-2" onsubmit="return confirm(@js(__('Owner override: set this status even though it skips the normal order flow?')))">
                    @csrf @method('PATCH')
                    <input type="hidden" name="override" value="1">
                    <select name="status" class="admin-input" aria-label="{{ __('Set any status') }}">
                        @foreach (\App\Enums\OrderStatus::cases() as $case)
                            <option value="{{ $case->value }}" @selected($order->status === $case)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                    <button class="admin-btn-outline">{{ __('Update status') }}</button>
                </form>
                @endif
            </section>

            <section class="admin-card p-4 text-sm sm:p-5">
                <h2 class="mb-3 text-[11px] font-semibold tracking-wider text-black/50 uppercase">{{ __('Customer') }}</h2>
                @if ($client)
                    <a href="{{ route('admin.clients.show', $client) }}" class="block text-base font-semibold hover:underline">{{ $client->displayName() }}</a>
                    @if ($client->vat_number)<p class="text-black/60">{{ __('VAT') }} {{ $client->vat_number }}</p>@endif
                    <p class="mt-1">{{ $client->fullName() }}</p>
                @else
                    <p class="text-base font-semibold">{{ $order->customer_name }}</p>
                    <p class="mt-1 rounded-md bg-amber-50 px-2.5 py-1.5 text-xs text-amber-900 ring-1 ring-amber-600/20 ring-inset">{{ __('Website order — call the customer to confirm items, total and delivery.') }}</p>
                @endif
                <p class="mt-2">{{ __('Priced as:') }} <x-admin.status-badge :status="$order->price_type" /> <span class="admin-badge bg-black/[0.03] text-black/70 ring-black/10">{{ $order->source->label() }}</span></p>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $order->customerPhone()) }}" class="admin-btn-outline"><x-admin.icon name="phone" class="size-4" /> {{ $order->customerPhone() }}</a>
                    @if ($client?->telegram_username)
                        <a href="https://t.me/{{ $client->telegram_username }}" target="_blank" rel="noopener" class="admin-btn-outline"><x-admin.icon name="send" class="size-4" /> Telegram</a>
                    @endif
                </div>
            </section>
        </aside>
    </div>

    {{-- Phones: next steps stay under the thumb --}}
    @if ($nextStatuses)
        <div class="sticky bottom-[calc(4.25rem+env(safe-area-inset-bottom))] z-10 -mx-4 mt-4 flex gap-2 border-t border-black/10 bg-white/95 px-4 py-3 lg:hidden">
            @foreach ($nextStatuses as $next)
                @include('admin.orders.partials.status-button', ['grow' => true])
            @endforeach
        </div>
    @endif
</x-admin.layout>
