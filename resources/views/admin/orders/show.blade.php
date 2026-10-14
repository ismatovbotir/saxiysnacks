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
                <h2 class="border-b border-black/10 px-4 py-3 font-semibold sm:px-5">{{ __('Items') }}</h2>

                <ul class="divide-y divide-black/5 md:hidden">
                    @foreach ($order->items as $item)
                        <li class="flex items-start justify-between gap-3 px-4 py-3 text-sm">
                            <span class="min-w-0">
                                <span class="block font-semibold">{{ $item->product_name }}</span>
                                <span class="block text-xs text-black/50">{{ $item->weight_grams }} {{ __('g') }}{{ $item->box_quantity ? ' · '.__(':count pcs per box', ['count' => $item->box_quantity]) : '' }}</span>
                                <span class="block text-black/70">{{ $item->quantity }} × {{ \App\Support\Money::format($item->unit_price) }}</span>
                            </span>
                            <span class="shrink-0 font-semibold">{{ \App\Support\Money::format($item->line_total) }}</span>
                        </li>
                    @endforeach
                </ul>

                <table class="hidden w-full text-left text-sm md:table">
                    <thead class="border-b border-black/10">
                        <tr><th class="admin-th">{{ __('Product') }}</th><th class="admin-th">{{ __('Boxes') }}</th><th class="admin-th">{{ __('Price per box') }}</th><th class="admin-th text-right">{{ __('Sum') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->product_name }}<span class="block text-xs text-black/50">{{ $item->weight_grams }} {{ __('g') }}{{ $item->box_quantity ? ' · '.__(':count pcs per box', ['count' => $item->box_quantity]) : '' }}</span></td>
                                <td class="px-4 py-3">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ \App\Support\Money::format($item->unit_price) }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">{{ \App\Support\Money::format($item->line_total) }}</td>
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

            <section class="admin-card p-4 sm:p-5">
                <h2 class="mb-3 font-semibold">{{ __('Delivery') }}</h2>
                <x-admin.location :latitude="$order->delivery_latitude" :longitude="$order->delivery_longitude" :address="$order->delivery_address" />
            </section>
        </div>

        <aside class="space-y-4 lg:space-y-6">
            <section class="admin-card p-4 sm:p-5">
                <h2 class="mb-3 font-semibold">{{ __('Status') }}</h2>
                @if ($nextStatuses)
                    <div class="hidden flex-wrap gap-2 lg:flex">
                        @foreach ($nextStatuses as $next)
                            @include('admin.orders.partials.status-button')
                        @endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 flex gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="admin-input" aria-label="{{ __('Set any status') }}">
                        @foreach (\App\Enums\OrderStatus::cases() as $case)
                            <option value="{{ $case->value }}" @selected($order->status === $case)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                    <button class="admin-btn-outline">{{ __('Update status') }}</button>
                </form>
            </section>

            <section class="admin-card p-4 text-sm sm:p-5">
                <h2 class="mb-3 font-semibold">{{ __('Customer') }}</h2>
                <a href="{{ route('admin.clients.show', $client) }}" class="block text-base font-semibold hover:underline">{{ $client->displayName() }}</a>
                @if ($client->vat_number)<p class="text-black/60">{{ __('VAT') }} {{ $client->vat_number }}</p>@endif
                <p class="mt-1">{{ $client->fullName() }}</p>
                <p class="mt-2">{{ __('Priced as:') }} <x-admin.status-badge :status="$order->price_type" /></p>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="tel:{{ $client->phone }}" class="admin-btn-outline"><x-admin.icon name="phone" class="size-4" /> {{ $client->phone }}</a>
                    @if ($client->telegram_username)
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
