<form method="POST" action="{{ route('admin.orders.status', $order) }}" @class(['flex-1' => $grow ?? false])>
    @csrf @method('PATCH')
    <button name="status" value="{{ $next->value }}"
            @if ($next === \App\Enums\OrderStatus::Cancelled) onclick="return confirm(@js(__('Cancel this order? The customer will be notified.')))" @endif
            @class([
                'w-full',
                'admin-btn-outline text-red-700' => $next === \App\Enums\OrderStatus::Cancelled,
                'admin-btn bg-leaf hover:bg-leaf-dark' => $next !== \App\Enums\OrderStatus::Cancelled,
            ])>{{ $next->emoji() }} {{ $next->label() }}</button>
</form>
