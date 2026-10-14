<x-admin.layout :title="$client->displayName()">
    <x-admin.page-header :title="$client->displayName()">
        <x-admin.status-badge :status="$client->type" />
        <x-admin.status-badge :status="$client->status" />
        @include('admin.clients.partials.actions')
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="admin-card p-5">
            <dl class="space-y-3 text-sm">
                @foreach ([
                    'Name' => $client->fullName(),
                    'Company' => $client->company_name ?? '—',
                    'VAT number' => $client->vat_number ?? '—',
                    'Prices' => $client->type->label(),
                    'Phone' => $client->phone,
                    'Telegram' => ($client->telegram_username ? '@'.$client->telegram_username.' · ' : '').'ID '.$client->telegram_id,
                    'Language' => strtoupper($client->language_code),
                    'Registered' => $client->created_at->format('d.m.Y H:i'),
                ] as $label => $value)
                    <div><dt class="text-black/50">{{ $label }}</dt><dd class="font-medium">{{ $value }}</dd></div>
                @endforeach
            </dl>
            <h3 class="mt-5 mb-2 text-sm text-black/50">Delivery location{{ $client->location_updated_at ? ' · updated '.$client->location_updated_at->format('d.m.Y') : '' }}</h3>
            <x-admin.location :latitude="$client->latitude" :longitude="$client->longitude" :address="$client->address" />
            @if ($client->telegram_username)
                <a href="https://t.me/{{ $client->telegram_username }}" target="_blank" rel="noopener" class="admin-btn-outline mt-4">Message in Telegram ↗</a>
            @endif
        </section>

        <section class="admin-card lg:col-span-2">
            <h2 class="border-b border-black/10 px-5 py-3 font-semibold">Orders</h2>
            <ul class="divide-y divide-black/5">
                @forelse ($orders as $order)
                    <li>
                        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between px-5 py-3 hover:bg-black/2">
                            <span><span class="font-semibold">{{ $order->number() }}</span> <span class="text-sm text-black/60">{{ $order->created_at->format('d.m.Y H:i') }}</span></span>
                            <span class="flex items-center gap-3">{{ \App\Support\Money::format($order->total) }} <x-admin.status-badge :status="$order->status" /></span>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-6 text-sm text-black/50">No orders yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-admin.layout>
