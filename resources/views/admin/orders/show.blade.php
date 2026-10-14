<x-admin.layout :title="'Order '.$order->number()">
    <x-admin.page-header :title="'Order '.$order->number()">
        <x-admin.status-badge :status="$order->status" />
        <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex gap-2">
            @csrf @method('PATCH')
            <select name="status" class="admin-input w-auto">
                @foreach (\App\Enums\OrderStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($order->status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <button class="admin-btn">Update status</button>
        </form>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="admin-card overflow-x-auto lg:col-span-2">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-black/10 text-black/60">
                    <tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">Boxes</th><th class="px-4 py-3">Price per box</th><th class="px-4 py-3 text-right">Sum</th></tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item->product_name }}<span class="block text-xs text-black/50">{{ $item->weight_grams }} g{{ $item->box_quantity ? ' · '.$item->box_quantity.' pcs per box' : '' }}</span></td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ \App\Support\Money::format($item->unit_price) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">{{ \App\Support\Money::format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-black/10 font-semibold">
                    <tr><td colspan="3" class="px-4 py-3">Total</td><td class="px-4 py-3 text-right whitespace-nowrap">{{ \App\Support\Money::format($order->total) }}</td></tr>
                </tfoot>
            </table>
            @if ($order->comment)
                <div class="border-t border-black/10 px-4 py-3 text-sm"><span class="text-black/50">Comment:</span> {{ $order->comment }}</div>
            @endif
        </section>

        <section class="admin-card p-5 text-sm">
            <h2 class="mb-3 font-semibold">Customer</h2>
            <p class="font-semibold"><a href="{{ route('admin.clients.show', $order->client) }}" class="hover:underline">{{ $order->client->displayName() }}</a></p>
            @if ($order->client->vat_number)<p class="text-black/60">VAT {{ $order->client->vat_number }}</p>@endif
            <p class="mt-2">Priced as: <x-admin.status-badge :status="$order->price_type" /></p>
            <p class="mt-2">{{ $order->client->fullName() }}</p>
            <p><a href="tel:{{ $order->client->phone }}" class="underline">{{ $order->client->phone }}</a></p>
            <p class="mt-3 text-black/50">Placed {{ $order->created_at->format('d.m.Y H:i') }}</p>
            <h2 class="mt-5 mb-2 font-semibold">Delivery</h2>
            <x-admin.location :latitude="$order->delivery_latitude" :longitude="$order->delivery_longitude" :address="$order->delivery_address" />
        </section>
    </div>
</x-admin.layout>
