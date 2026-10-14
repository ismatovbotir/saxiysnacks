<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ChangeOrderStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = OrderStatus::tryFrom($request->string('status')->value());

        $orders = Order::query()
            ->with('client')
            ->withCount('items')
            ->when($status, fn ($query, OrderStatus $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders, 'status' => $status]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load(['client', 'items', 'payments.user'])]);
    }

    public function updateStatus(Request $request, Order $order, ChangeOrderStatus $changeStatus): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::enum(OrderStatus::class)]]);

        $override = $request->boolean('override') && $request->user()->role === UserRole::Owner;

        $changeStatus->handle($order, OrderStatus::from($validated['status']), $override);

        return back()->with('status', __('Order :number is now :status.', ['number' => $order->number(), 'status' => $order->status->label()]));
    }
}
