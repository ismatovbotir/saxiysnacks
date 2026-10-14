<div class="flex flex-wrap gap-2 sm:justify-end">
    <form method="POST" action="{{ route('admin.clients.type', $client) }}">
        @csrf @method('PATCH')
        @if ($client->isReseller())
            <button name="type" value="consumer" class="admin-btn-outline px-3 py-1.5">👤 {{ __('Make consumer') }}</button>
        @else
            <button name="type" value="reseller" class="admin-btn bg-brand px-3 py-1.5 text-ink hover:bg-brand-dark hover:text-white">⭐ {{ __('Make reseller') }}</button>
        @endif
    </form>
    <form method="POST" action="{{ route('admin.clients.status', $client) }}">
        @csrf @method('PATCH')
        @if ($client->isActive())
            <button name="status" value="blocked" class="admin-btn-outline px-3 py-1.5 text-red-700" onclick="return confirm(@js(__('Block this customer? They will no longer see prices or be able to order.')))">{{ __('Block') }}</button>
        @else
            <button name="status" value="active" class="admin-btn bg-leaf px-3 py-1.5 hover:bg-leaf-dark">{{ __('Unblock') }}</button>
        @endif
    </form>
</div>
