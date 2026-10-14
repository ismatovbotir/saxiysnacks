<x-admin.layout title="Customers">
    <x-admin.page-header title="Customers" />

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Name, phone, company, VAT" class="admin-input max-w-xs">
        <select name="type" class="admin-input max-w-48" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach (\App\Enums\ClientType::cases() as $case)
                <option value="{{ $case->value }}" @selected($type === $case)>{{ $case->label() }}s</option>
            @endforeach
        </select>
        <select name="status" class="admin-input max-w-48" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Enums\ClientStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
            @endforeach
        </select>
        <button class="admin-btn-outline">Filter</button>
    </form>

    <div class="admin-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-black/10 text-black/60">
                <tr><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Company</th><th class="px-4 py-3">Prices</th><th class="px-4 py-3">Orders</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Registered</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @forelse ($clients as $client)
                    <tr>
                        <td class="px-4 py-3"><a href="{{ route('admin.clients.show', $client) }}" class="font-semibold hover:underline">{{ $client->fullName() }}</a><span class="block text-xs text-black/50">{{ $client->phone }}{{ $client->telegram_username ? ' · @'.$client->telegram_username : '' }}</span></td>
                        <td class="px-4 py-3">{{ $client->company_name ?? '—' }}@if ($client->vat_number)<span class="block font-mono text-xs text-black/50">VAT {{ $client->vat_number }}</span>@endif</td>
                        <td class="px-4 py-3"><x-admin.status-badge :status="$client->type" /></td>
                        <td class="px-4 py-3">{{ $client->orders_count }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :status="$client->status" /></td>
                        <td class="px-4 py-3 whitespace-nowrap text-black/60">{{ $client->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-3 text-right">@include('admin.clients.partials.actions')</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-black/50">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</x-admin.layout>
